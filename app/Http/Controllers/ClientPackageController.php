<?php

namespace App\Http\Controllers;

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
            if (! is_string($sessionTokenInput) || $sessionTokenInput === '' || mb_strlen($sessionTokenInput) > 128) {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            $releaseChannelInput = $request->query('release_channel', 'stable');
            if (! is_string($releaseChannelInput) || ! in_array($releaseChannelInput, ['stable', 'dev'], true)) {
                return $this->errorResponse(422, 'INVALID_CHANNEL', 'Release channel is invalid.');
            }

            $sessionToken = $sessionTokenInput;
            $session = ClientSession::query()
                ->with('account')
                ->where('session_token', $sessionToken)
                ->first();

            if (! $session || ! $session->account) {
                return $this->errorResponse(401, 'AUTH_REQUIRED', 'Authentication required.');
            }

            if (! $session->account->hasPrivilege(1)) {
                return $this->errorResponse(403, 'LICENSE_INEFFECTIVE', 'License is not effective.');
            }

            $latestRelease = PackageService::getLatestRelease($releaseChannelInput);

            if (! $latestRelease) {
                return $this->errorResponse(404, 'PACKAGE_NOT_FOUND', 'No package release found for this channel.');
            }

            return response()->json([
                'code' => 200,
                'error_code' => null,
                'message' => 'OK',
                'data' => [
                    'version' => $latestRelease->version,
                    'release_channel' => $latestRelease->release_channel,
                    'download_url' => $latestRelease->download_url,
                    'changelog' => $latestRelease->changelog,
                    'virus_detection_url' => $latestRelease->virus_detection_url,
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
