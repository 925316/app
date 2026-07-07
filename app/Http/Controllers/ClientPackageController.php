<?php

namespace App\Http\Controllers;

use App\Enums\ApiErrorCode;
use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\ClientUpdateCheckRequest;
use App\Models\ClientSession;
use App\Services\CryptoService;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ClientPackageController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CryptoService $cryptoService) {}

    public function check(ClientUpdateCheckRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $sessionToken = $validated['session_token'] ?? null;
            if (! is_string($sessionToken) || $sessionToken === '') {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $releaseChannel = $validated['release_channel'] ?? 'stable';
            if (! is_string($releaseChannel) || ! in_array($releaseChannel, ['stable', 'dev'], true)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_CHANNEL, 'Invalid release channel.', true);
            }

            $currentVersion = $validated['current_version'] ?? null;
            if ($currentVersion !== null && $currentVersion !== '' && ! PackageService::isValidSemanticVersion($currentVersion)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_VERSION, 'Invalid version format.', true);
            }

            $session = ClientSession::query()
                ->with(['account', 'device'])
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session || ! $session->account) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            $sessionDevice = $session->device;
            if (! $sessionDevice || $sessionDevice->bound_at === null || $sessionDevice->unbound_at !== null) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.', true);
            }

            if (! $session->account->hasPrivilege(1)) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.', true);
            }

            $latestRelease = PackageService::getLatestRelease($releaseChannel);

            if (! $latestRelease) {
                return $this->errorResponse(404, ApiErrorCode::PACKAGE_NOT_FOUND, 'No package release found for this channel.', true);
            }

            if (! PackageService::isSafePublicHttpsUrl((string) $latestRelease->download_url)) {
                return $this->errorResponse(404, ApiErrorCode::PACKAGE_NOT_FOUND, 'No package release found for this channel.', true);
            }

            $virusDetectionUrl = $latestRelease->virus_detection_url;
            if (is_string($virusDetectionUrl) && $virusDetectionUrl !== '' && ! PackageService::isSafePublicHttpsUrl($virusDetectionUrl)) {
                return $this->errorResponse(404, ApiErrorCode::PACKAGE_NOT_FOUND, 'No package release found for this channel.', true);
            }

            $updateAvailable = $currentVersion !== null
                ? PackageService::compareReleaseVersions($latestRelease->version, $currentVersion) > 0
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
                'virus_detection_url' => $virusDetectionUrl,
            ];

            return $this->successResponse($data, true);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.', true);
        }
    }
}
