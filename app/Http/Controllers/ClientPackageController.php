<?php

namespace App\Http\Controllers;

use App\Enums\ApiErrorCode;
use App\Models\ClientSession;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ClientPackageController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        try {
            $sessionTokenInput = $request->query('session_token');
            if (is_string($sessionTokenInput)) {
                $sessionTokenInput = trim($sessionTokenInput);
            }

            if (! is_string($sessionTokenInput) || $sessionTokenInput === '' || mb_strlen($sessionTokenInput) > 128) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.');
            }

            $releaseChannelInput = $request->query('release_channel', 'stable');
            if (is_string($releaseChannelInput)) {
                $releaseChannelInput = strtolower(trim($releaseChannelInput));
            }

            if (! is_string($releaseChannelInput) || ! in_array($releaseChannelInput, ['stable', 'dev'], true)) {
                return $this->errorResponse(422, ApiErrorCode::INVALID_CHANNEL, 'Release channel is invalid.');
            }

            $currentVersionInput = $request->query('current_version');
            if ($currentVersionInput !== null) {
                if (is_string($currentVersionInput)) {
                    $currentVersionInput = trim($currentVersionInput);
                }

                if (! is_string($currentVersionInput) || ! PackageService::isValidSemanticVersion($currentVersionInput)) {
                    return $this->errorResponse(422, ApiErrorCode::INVALID_VERSION, 'Current version format is invalid.');
                }
            }

            $sessionToken = $sessionTokenInput;
            $session = ClientSession::query()
                ->with('account')
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session || ! $session->account) {
                return $this->errorResponse(401, ApiErrorCode::AUTH_REQUIRED, 'Authentication required.');
            }

            if (! $session->account->hasPrivilege(1)) {
                return $this->errorResponse(403, ApiErrorCode::LICENSE_INEFFECTIVE, 'License is not effective.');
            }

            $latestRelease = PackageService::getLatestRelease($releaseChannelInput);

            if (! $latestRelease) {
                return $this->errorResponse(404, ApiErrorCode::PACKAGE_NOT_FOUND, 'No package release found for this channel.');
            }

            $currentVersion = is_string($currentVersionInput) ? $currentVersionInput : null;
            $updateAvailable = $currentVersion !== null
                ? version_compare($latestRelease->version, $currentVersion, '>')
                : null;
            $reason = $currentVersion === null
                ? 'no_current_version'
                : ($updateAvailable ? 'newer_available' : 'up_to_date');

            return response()->json([
                'code' => 200,
                'error_code' => null,
                'message' => 'OK',
                'data' => [
                    'current_version' => $currentVersion,
                    'version' => $latestRelease->version,
                    'release_channel' => $latestRelease->release_channel,
                    'update_available' => $updateAvailable,
                    'reason' => $reason,
                    'download_url' => $latestRelease->download_url,
                    'changelog' => $latestRelease->changelog,
                    'virus_detection_url' => $latestRelease->virus_detection_url,
                ],
            ], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->errorResponse(500, ApiErrorCode::SERVER_ERROR, 'Internal server error.');
        }
    }

    private function errorResponse(int $httpCode, ApiErrorCode $errorCode, string $message): JsonResponse
    {
        return response()->json([
            'code' => $httpCode,
            'error_code' => $errorCode->value,
            'message' => $message,
            'data' => null,
        ], $httpCode);
    }
}
