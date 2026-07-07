<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\ApiErrorCode;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    private const SIGNING_ALGORITHM = 'RSA-2048-SHA256';

    protected function errorResponse(int $httpCode, ApiErrorCode $errorCode, string $message, bool $signResponse = false): JsonResponse
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
    protected function successResponse(array $data, bool $signResponse = false): JsonResponse
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
