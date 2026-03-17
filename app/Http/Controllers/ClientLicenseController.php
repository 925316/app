<?php

namespace App\Http\Controllers;

use App\Enums\ApiErrorCode;
use App\Enums\LicenseStatus;
use App\Http\Requests\ClientActivateRequest;
use App\Http\Requests\ClientHeartbeatRequest;
use App\Http\Requests\ClientLoginRequest;
use App\Http\Requests\ClientUnbindRequest;
use App\Models\Account;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Models\License;
use App\Services\CryptoService;
use App\Services\LicenseService;
use App\Services\NonceGuardService;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClientLicenseController extends Controller
{
    private const SIGNING_ALGORITHM = 'RSA-2048-SHA256';

    public function __construct(
        private readonly NonceGuardService $nonceGuardService,
        private readonly CryptoService $cryptoService,
        private readonly AuthManager $authManager,
    ) {}

    public function login(ClientLoginRequest $request): JsonResponse
    {
        try {
            $rateLimitResponse = $this->enforceRateLimit($request, 'account.login', 7, 60);
            if ($rateLimitResponse !== null) {
                return $rateLimitResponse;
            }

            $validated = $request->validated();
            $currentTime = now();

            $email = $validated['email'] ?? null;
            $password = $validated['password'] ?? null;
            if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $timestamp = (int) $validated['timestamp'];
            if (abs($currentTime->timestamp - $timestamp) > 300) {
                return $this->errorResponse(422, ApiErrorCode::TIMESTAMP_OUT_OF_WINDOW, 'Timestamp is out of allowed window.', true);
            }

            $nonceScope = 'account.login|'.sha1($email);
            $nonceAcquired = $this->nonceGuardService->acquire($nonceScope, (string) $validated['nonce']);
            if (! $nonceAcquired) {
                return $this->errorResponse(409, ApiErrorCode::NONCE_REPLAY, 'Nonce has already been used.', true);
            }

            $credentials = [
                'email' => $email,
                'password' => $password,
            ];

            if (! $this->authManager->guard('web')->validate($credentials)) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $account = Account::query()->where('email', $email)->first();
            if (! $account) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            if ($account->isSuspended()) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            if (! $account->hasPrivilege(1)) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            $session = DB::transaction(function () use ($account, $validated, $request, $currentTime) {
                $activeDevice = $account->devices()
                    ->whereNotNull('bound_at')
                    ->whereNull('unbound_at')
                    ->lockForUpdate()
                    ->first();

                $incomingHwidHash = hash('sha256', (string) $validated['hwid']);

                if (! $activeDevice) {
                    return null;
                }

                if (! is_string($activeDevice->hwid_hash) || ! hash_equals($activeDevice->hwid_hash, $incomingHwidHash)) {
                    return false;
                }

                $activeDevice->forceFill([
                    'ip_address' => $request->ip(),
                    'country_code' => $validated['country_code'] ?? $activeDevice->country_code,
                    'last_seen_at' => $currentTime,
                ])->save();

                ClientSession::query()
                    ->where('account_id', $account->id)
                    ->where('device_id', $activeDevice->id)
                    ->delete();

                $newSession = ClientSession::query()->create([
                    'session_token' => (string) Str::uuid(),
                    'account_id' => $account->id,
                    'device_id' => $activeDevice->id,
                    'ip_address' => $request->ip(),
                    'client_version' => (string) $validated['version'],
                    'last_heartbeat_at' => $currentTime,
                ]);

                EventLog::query()->create([
                    'event_type' => 'account.login',
                    'event_level' => EventLog::LEVEL_INFO,
                    'account_id' => $account->id,
                    'actor_id' => $account->id,
                    'ip_address' => $request->ip(),
                    'details' => [
                        'device_id' => $activeDevice->id,
                        'session_token' => $newSession->session_token,
                        'client_version' => $validated['version'],
                    ],
                ]);

                return $newSession;
            });

            if ($session === null) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(422, ApiErrorCode::DEVICE_MISMATCH, 'Device does not match bound device.', true);
            }

            if ($session === false) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(422, ApiErrorCode::DEVICE_MISMATCH, 'Device does not match bound device.', true);
            }

            $account->recordLogin((string) $request->ip(), (string) $request->userAgent());

            $effectiveLicense = License::query()
                ->where('used_by', $account->id)
                ->where('status', LicenseStatus::ACTIVE->value)
                ->where('expires_at', '>', $currentTime)
                ->orderByDesc('privilege')
                ->first();

            if (! $effectiveLicense || $effectiveLicense->expires_at === null) {
                $this->recordRateLimitFailure($request, 'account.login', 60);

                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            $this->clearRateLimit($request, 'account.login');

            $data = [
                'session_token' => $session->session_token,
                'account' => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'email' => $account->email,
                ],
                'license' => [
                    'license_key' => $effectiveLicense->key,
                    'plan_level' => (int) ($effectiveLicense->privilege?->value ?? 0),
                    'status' => 'active',
                    'expires_at' => $effectiveLicense->expires_at->format('Y-m-d H:i:s'),
                    'expires_timestamp' => $effectiveLicense->expires_at->timestamp,
                ],
            ];

            return $this->successResponse($data, true);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.', true);
        }
    }

    public function check(ClientHeartbeatRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currentTime = now();

            $sessionToken = $validated['session_token'] ?? null;
            if (! is_string($sessionToken) || $sessionToken === '') {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $licenseKey = (string) $validated['license_key'];
            if (! LicenseService::validateLicenseKeyFormat($licenseKey)) {
                return $this->errorResponse(422, ApiErrorCode::LICENSE_INVALID, 'License key is invalid.', true);
            }

            $timestamp = (int) $validated['timestamp'];
            if (abs($currentTime->timestamp - $timestamp) > 300) {
                return $this->errorResponse(422, ApiErrorCode::TIMESTAMP_OUT_OF_WINDOW, 'Timestamp is out of allowed window.', true);
            }

            $nonceScope = 'license.check|'.$sessionToken;
            $nonceAcquired = $this->nonceGuardService->acquire($nonceScope, (string) $validated['nonce']);
            if (! $nonceAcquired) {
                return $this->errorResponse(409, ApiErrorCode::NONCE_REPLAY, 'Nonce has already been used.', true);
            }

            $session = ClientSession::query()
                ->with(['device', 'account'])
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $license = License::query()->where('key', $licenseKey)->first();
            if (! $license) {
                return $this->errorResponse(422, ApiErrorCode::LICENSE_INVALID, 'License key is invalid.', true);
            }

            if (
                $license->status !== LicenseStatus::ACTIVE
                || $license->expires_at === null
                || $license->expires_at->lte($currentTime)
                || $license->used_by === null
            ) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            if ($license->used_by !== $session->account_id) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $incomingHwidHash = hash('sha256', (string) $validated['hwid']);
            $boundDevice = $session->device;

            if (! $boundDevice || ! is_string($boundDevice->hwid_hash) || ! hash_equals($boundDevice->hwid_hash, $incomingHwidHash)) {
                return $this->errorResponse(422, ApiErrorCode::DEVICE_MISMATCH, 'Device does not match bound device.', true);
            }

            $data = [
                'status' => 'active',
                'expires_at' => $license->expires_at->format('Y-m-d H:i:s'),
                'expires_timestamp' => $license->expires_at->timestamp,
                'plan_level' => (int) ($license->privilege?->value ?? 0),
                'username' => (string) ($session->account?->username ?? ''),
            ];

            $signature = $this->cryptoService->signData($data);

            $session->forceFill([
                'last_heartbeat_at' => $currentTime,
            ])->save();

            return $this->successResponse($data, true);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.', true);
        }
    }

    public function activate(ClientActivateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currentTime = now();

            $sessionToken = $validated['session_token'] ?? null;
            if (! is_string($sessionToken) || $sessionToken === '') {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $timestamp = (int) $validated['timestamp'];
            if (abs($currentTime->timestamp - $timestamp) > 300) {
                return $this->errorResponse(422, ApiErrorCode::TIMESTAMP_OUT_OF_WINDOW, 'Timestamp is out of allowed window.', true);
            }

            $nonceScope = 'license.activate|'.$sessionToken;
            $nonceAcquired = $this->nonceGuardService->acquire($nonceScope, (string) $validated['nonce']);
            if (! $nonceAcquired) {
                return $this->errorResponse(409, ApiErrorCode::NONCE_REPLAY, 'Nonce has already been used.', true);
            }

            $session = ClientSession::query()
                ->with(['device', 'account'])
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $boundDevice = $session->device;
            $incomingHwidHash = hash('sha256', (string) $validated['hwid']);
            if (! $boundDevice || ! is_string($boundDevice->hwid_hash) || ! hash_equals($boundDevice->hwid_hash, $incomingHwidHash)) {
                return $this->errorResponse(422, ApiErrorCode::DEVICE_MISMATCH, 'Device does not match bound device.', true);
            }

            $license = License::query()->where('key', (string) $validated['license_key'])->first();
            if (! $license) {
                return $this->errorResponse(422, ApiErrorCode::LICENSE_INVALID, 'License key is invalid.', true);
            }

            if ($license->isExpired()) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            if (! $license->canActivate()) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            $account = $session->account;
            if (! $account instanceof Account) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $activeLicense = License::query()
                ->where('used_by', $account->id)
                ->where('status', LicenseStatus::ACTIVE->value)
                ->where('expires_at', '>', $currentTime)
                ->first();

            if ($activeLicense && $activeLicense->id !== $license->id) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            if (! $license->canActivateByPrivilege()) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            try {
                LicenseService::activateLicense($license, $account, $request->ip());
            } catch (ValidationException) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            $freshLicense = $license->fresh();
            if (! $freshLicense || $freshLicense->expires_at === null) {
                return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.', true);
            }

            $data = [
                'status' => 'active',
                'expires_at' => $freshLicense->expires_at->format('Y-m-d H:i:s'),
                'expires_timestamp' => $freshLicense->expires_at->timestamp,
                'plan_level' => (int) ($freshLicense->privilege?->value ?? 0),
                'username' => (string) ($account->username ?? ''),
            ];

            return $this->successResponse($data, true);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.', true);
        }
    }

    public function unbind(ClientUnbindRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $currentTime = now();

            $sessionTokenValue = $validated['session_token'] ?? null;
            if (! is_string($sessionTokenValue) || $sessionTokenValue === '') {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $sessionToken = $sessionTokenValue;

            $timestamp = (int) $validated['timestamp'];
            if (abs($currentTime->timestamp - $timestamp) > 300) {
                return $this->errorResponse(422, ApiErrorCode::TIMESTAMP_OUT_OF_WINDOW, 'Timestamp is out of allowed window.', true);
            }

            $nonceScope = 'license.unbind|'.$sessionToken;
            $nonceAcquired = $this->nonceGuardService->acquire($nonceScope, (string) $validated['nonce']);
            if (! $nonceAcquired) {
                return $this->errorResponse(409, ApiErrorCode::NONCE_REPLAY, 'Nonce has already been used.', true);
            }

            $session = ClientSession::query()
                ->with(['device', 'account'])
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $licenseKey = (string) $validated['license_key'];
            if (! LicenseService::validateLicenseKeyFormat($licenseKey)) {
                return $this->errorResponse(422, ApiErrorCode::LICENSE_INVALID, 'License key is invalid.', true);
            }

            $license = License::query()->where('key', $licenseKey)->first();
            if (! $license) {
                return $this->errorResponse(422, ApiErrorCode::LICENSE_INVALID, 'License key is invalid.', true);
            }

            if (
                $license->status !== LicenseStatus::ACTIVE
                || $license->expires_at === null
                || $license->expires_at->lte($currentTime)
                || $license->used_by === null
            ) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            if ($license->used_by !== $session->account_id) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $incomingHwidHash = hash('sha256', (string) $validated['hwid']);
            $boundDevice = $session->device;

            if (! $boundDevice || ! is_string($boundDevice->hwid_hash) || ! hash_equals($boundDevice->hwid_hash, $incomingHwidHash)) {
                return $this->errorResponse(422, ApiErrorCode::DEVICE_MISMATCH, 'Device does not match bound device.', true);
            }

            $unboundDevice = DB::transaction(function () use ($session, $license, $request, $currentTime) {
                $lockedDevice = $session->device()
                    ->lockForUpdate()
                    ->first();

                if (! $lockedDevice || $lockedDevice->bound_at === null || $lockedDevice->unbound_at !== null) {
                    return null;
                }

                $lockedDevice->forceFill([
                    'unbound_at' => $currentTime,
                ])->save();

                ClientSession::query()
                    ->where('account_id', $session->account_id)
                    ->where('device_id', $lockedDevice->id)
                    ->delete();

                EventLog::query()->create([
                    'event_type' => 'device.unbound',
                    'event_level' => EventLog::LEVEL_INFO,
                    'account_id' => $session->account_id,
                    'license_id' => $license->id,
                    'ip_address' => $request->ip(),
                    'actor_id' => $session->account_id,
                    'details' => [
                        'device_id' => $lockedDevice->id,
                        'hwid_hash' => $lockedDevice->hwid_hash,
                        'session_token' => $session->session_token,
                    ],
                ]);

                return $lockedDevice;
            });

            if (! $unboundDevice) {
                return $this->errorResponse(409, ApiErrorCode::DEVICE_NOT_BOUND, 'No active bound device was found.', true);
            }

            $data = [
                'status' => 'unbound',
                'license_key' => $license->key,
                'device_id' => $unboundDevice->id,
                'unbound_at' => $unboundDevice->unbound_at?->format('Y-m-d H:i:s'),
                'unbound_timestamp' => $unboundDevice->unbound_at?->timestamp,
            ];

            return $this->successResponse($data, true);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.', true);
        }
    }

    private function errorResponse(int $httpCode, ApiErrorCode $errorCode, string $message, bool $signResponse = false): JsonResponse
    {
        $payload = [
            'code' => $httpCode,
            'error_code' => $errorCode->value,
            'message' => $message,
            'data' => null,
        ];

        if ($signResponse) {
            $payload['signature'] = $this->cryptoService->signData($payload['data']);
            $payload['meta'] = [
                'signature' => [
                    'algorithm' => self::SIGNING_ALGORITHM,
                    'key_id' => (string) config('services.api_signing.key_id', 'main-2026-01'),
                ],
            ];
        }

        return response()->json($payload, $httpCode);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function successResponse(array $data, bool $signResponse = false): JsonResponse
    {
        $payload = [
            'code' => 200,
            'error_code' => null,
            'message' => 'OK',
            'data' => $data,
        ];

        if ($signResponse) {
            $payload['signature'] = $this->cryptoService->signData($data);
            $payload['meta'] = [
                'signature' => [
                    'algorithm' => self::SIGNING_ALGORITHM,
                    'key_id' => (string) config('services.api_signing.key_id', 'main-2026-01'),
                ],
            ];
        }

        return response()->json($payload, 200);
    }

    private function enforceRateLimit(ClientLoginRequest $request, string $scope, int $maxAttempts, int $decaySeconds): ?JsonResponse
    {
        $email = $request->input('email');
        $normalizedEmail = is_string($email) ? strtolower(trim($email)) : '';
        $key = $normalizedEmail !== ''
            ? sprintf('api:%s:%s|%s', $scope, $normalizedEmail, (string) $request->ip())
            : sprintf('api:%s:ip|%s', $scope, (string) $request->ip());

        if (! \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return null;
        }

        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);

        return $this->errorResponse(429, ApiErrorCode::RATE_LIMITED, sprintf('Too many attempts. Try again in %d seconds.', $seconds), true);
    }

    private function recordRateLimitFailure(ClientLoginRequest $request, string $scope, int $decaySeconds): void
    {
        $email = $request->input('email');
        $normalizedEmail = is_string($email) ? strtolower(trim($email)) : '';
        $key = $normalizedEmail !== ''
            ? sprintf('api:%s:%s|%s', $scope, $normalizedEmail, (string) $request->ip())
            : sprintf('api:%s:ip|%s', $scope, (string) $request->ip());

        \Illuminate\Support\Facades\RateLimiter::hit($key, $decaySeconds);
    }

    private function clearRateLimit(ClientLoginRequest $request, string $scope): void
    {
        $email = $request->input('email');
        $normalizedEmail = is_string($email) ? strtolower(trim($email)) : '';
        $key = $normalizedEmail !== ''
            ? sprintf('api:%s:%s|%s', $scope, $normalizedEmail, (string) $request->ip())
            : sprintf('api:%s:ip|%s', $scope, (string) $request->ip());

        \Illuminate\Support\Facades\RateLimiter::clear($key);
    }
}
