<?php

namespace App\Http\Controllers;

use App\Enums\ApiErrorCode;
use App\Http\Requests\ClientUpdateCheckRequest;
use App\Models\ClientSession;
use App\Services\CryptoService;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ClientPackageController extends Controller
{
    private const SIGNING_ALGORITHM = 'RSA-2048-SHA256';

    public function __construct(private readonly CryptoService $cryptoService) {}

    public function check(ClientUpdateCheckRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $sessionToken = $validated['session_token'] ?? null;
            $releaseChannel = $validated['release_channel'] ?? 'stable';
            $currentVersion = $validated['current_version'] ?? null;

            if (! is_string($releaseChannel)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_CHANNEL, 'Release channel is invalid.', true);
            }

            if (! is_string($sessionToken) || $sessionToken === '' || mb_strlen($sessionToken) > 128) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            if ($currentVersion !== null && (! is_string($currentVersion) || mb_strlen($currentVersion) > 50)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_VERSION, 'Current version format is invalid.', true);
            }

            if ($currentVersion !== null && ! PackageService::isValidSemanticVersion($currentVersion)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_VERSION, 'Current version format is invalid.', true);
            }

            if (mb_strlen($releaseChannel) > 20 || ! in_array($releaseChannel, ['stable', 'dev'], true)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_CHANNEL, 'Release channel is invalid.', true);
            }

            $session = ClientSession::query()
                ->with('account')
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session || ! $session->account) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            if (! $session->account->hasPrivilege(1)) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            $latestRelease = PackageService::getLatestRelease($releaseChannel);

            if (! $latestRelease) {
                return $this->errorResponse(404, ApiErrorCode::PACKAGE_NOT_FOUND, 'No package release found for this channel.', true);
            }

            $updateAvailable = $currentVersion !== null
                ? version_compare($latestRelease->version, $currentVersion, '>')
                : null;
            $reason = $currentVersion === null
                ? 'no_current_version'
                : ($updateAvailable ? 'newer_available' : 'up_to_date');

            $data = [
                'current_version' => $currentVersion,
                'version' => $latestRelease->version,
                'release_channel' => $latestRelease->release_channel,
                'update_available' => $updateAvailable,
                'reason' => $reason,
                'download_url' => $latestRelease->download_url,
                'changelog' => $latestRelease->changelog,
                'virus_detection_url' => $latestRelease->virus_detection_url,
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
}
