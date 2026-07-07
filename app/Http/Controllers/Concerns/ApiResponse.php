<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\ApiErrorCode;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
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
                    'algorithm' => $this->cryptoService->algorithm(),
                    'key_id' => $this->cryptoService->keyId(),
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
                    'algorithm' => $this->cryptoService->algorithm(),
                    'key_id' => $this->cryptoService->keyId(),
                ],
            ];
        }

        return response()->json($payload, 200);
    }
}
