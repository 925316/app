<?php

namespace App\Http\Controllers;

use App\Enums\LicenseStatus;
use App\Http\Requests\ClientActivateRequest;
use App\Http\Requests\ClientHeartbeatRequest;
use App\Models\Account;
use App\Models\ClientSession;
use App\Models\License;
use App\Services\CryptoService;
use App\Services\LicenseService;
use App\Services\NonceGuardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClientLicenseController extends Controller
{
    public function __construct(
        private readonly NonceGuardService $nonceGuardService,
        private readonly CryptoService $cryptoService,
    ) {}

    public function check(ClientHeartbeatRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $sessionToken = $validated['session_token'] ?? null;
            if (! is_string($sessionToken) || $sessionToken === '') {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $licenseKey = (string) $validated['license_key'];
            if (! LicenseService::validateLicenseKeyFormat($licenseKey)) {
                return $this->errorResponse(422, 'LICENSE_INVALID', 'License key is invalid.');
            }

            $timestamp = (int) $validated['timestamp'];
            if (abs(now()->timestamp - $timestamp) > 300) {
                return $this->errorResponse(422, 'TIMESTAMP_OUT_OF_WINDOW', 'Timestamp is out of allowed window.');
            }

            $nonceScope = 'license.check|'.$sessionToken;
            $nonceAcquired = $this->nonceGuardService->acquire($nonceScope, (string) $validated['nonce']);
            if (! $nonceAcquired) {
                return $this->errorResponse(409, 'NONCE_REPLAY', 'Nonce has already been used.');
            }

            $session = ClientSession::query()
                ->with(['device', 'account'])
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session) {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $license = License::query()->where('key', $licenseKey)->first();
            if (! $license) {
                return $this->errorResponse(422, 'LICENSE_INVALID', 'License key is invalid.');
            }

            if (
                $license->status !== LicenseStatus::ACTIVE
                || $license->expires_at === null
                || $license->expires_at->lte(now())
                || $license->used_by === null
            ) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            if ($license->used_by !== $session->account_id) {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $incomingHwidHash = hash('sha256', (string) $validated['hwid']);
            $boundDevice = $session->device;

            if (! $boundDevice || ! is_string($boundDevice->hwid_hash) || ! hash_equals($boundDevice->hwid_hash, $incomingHwidHash)) {
                return $this->errorResponse(422, 'DEVICE_MISMATCH', 'Device does not match bound device.');
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
                'last_heartbeat_at' => now(),
            ])->save();

            return response()->json([
                'code' => 200,
                'error_code' => null,
                'message' => 'OK',
                'data' => $data,
                'signature' => $signature,
                'meta' => [
                    'signature' => [
                        'algorithm' => (string) config('services.api_signing.algorithm', 'RSA-2048-SHA256'),
                        'key_id' => (string) config('services.api_signing.key_id', 'main-2026-01'),
                    ],
                ],
            ], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, 'SERVER_ERROR', 'Internal server error.');
        }
    }

    public function activate(ClientActivateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $sessionToken = $validated['session_token'] ?? null;
            if (! is_string($sessionToken) || $sessionToken === '') {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $timestamp = (int) $validated['timestamp'];
            if (abs(now()->timestamp - $timestamp) > 300) {
                return $this->errorResponse(422, 'TIMESTAMP_OUT_OF_WINDOW', 'Timestamp is out of allowed window.');
            }

            $nonceScope = 'license.activate|'.$sessionToken;
            $nonceAcquired = $this->nonceGuardService->acquire($nonceScope, (string) $validated['nonce']);
            if (! $nonceAcquired) {
                return $this->errorResponse(409, 'NONCE_REPLAY', 'Nonce has already been used.');
            }

            $session = ClientSession::query()
                ->with(['device', 'account'])
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session) {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $boundDevice = $session->device;
            $incomingHwidHash = hash('sha256', (string) $validated['hwid']);
            if (! $boundDevice || ! is_string($boundDevice->hwid_hash) || ! hash_equals($boundDevice->hwid_hash, $incomingHwidHash)) {
                return $this->errorResponse(422, 'DEVICE_MISMATCH', 'Device does not match bound device.');
            }

            $license = License::query()->where('key', (string) $validated['license_key'])->first();
            if (! $license) {
                return $this->errorResponse(422, 'LICENSE_INVALID', 'License key is invalid.');
            }

            if ($license->isExpired()) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            if (! $license->canActivate()) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            $account = $session->account;
            if (! $account instanceof Account) {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $activeLicense = License::query()
                ->where('used_by', $account->id)
                ->where('status', LicenseStatus::ACTIVE->value)
                ->where('expires_at', '>', now())
                ->first();

            if ($activeLicense && $activeLicense->id !== $license->id) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            if (! $license->canActivateByPrivilege()) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            try {
                LicenseService::activateLicense($license, $account, $request->ip());
            } catch (ValidationException) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            $freshLicense = $license->fresh();
            if (! $freshLicense || $freshLicense->expires_at === null) {
                return $this->errorResponse(500, 'SERVER_ERROR', 'Internal server error.');
            }

            $data = [
                'status' => 'active',
                'expires_at' => $freshLicense->expires_at->format('Y-m-d H:i:s'),
                'expires_timestamp' => $freshLicense->expires_at->timestamp,
                'plan_level' => (int) ($freshLicense->privilege?->value ?? 0),
                'username' => (string) ($account->username ?? ''),
            ];

            $signature = $this->cryptoService->signData($data);

            return response()->json([
                'code' => 200,
                'error_code' => null,
                'message' => 'OK',
                'data' => $data,
                'signature' => $signature,
                'meta' => [
                    'signature' => [
                        'algorithm' => (string) config('services.api_signing.algorithm', 'RSA-2048-SHA256'),
                        'key_id' => (string) config('services.api_signing.key_id', 'main-2026-01'),
                    ],
                ],
            ], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, 'SERVER_ERROR', 'Internal server error.');
        }
    }

    private function errorResponse(int $httpCode, string $errorCode, string $message): JsonResponse
    {
        return response()->json([
            'code' => $httpCode,
            'error_code' => $errorCode,
            'message' => $message,
            'data' => null,
        ], $httpCode);
    }
}
