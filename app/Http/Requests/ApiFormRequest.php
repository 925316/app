<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $firstMessage = $validator->errors()->first() ?? 'Invalid request.';

        $payload = [
            'code' => 422,
            'error_code' => \App\Enums\ApiErrorCode::VALIDATION_FAILED->value,
            'message' => $firstMessage,
            'data' => null,
        ];

        $payload['signature'] = app(\App\Services\CryptoService::class)->signData($payload['data']);
        $payload['meta'] = [
            'signature' => [
                'algorithm' => 'RSA-2048-SHA256',
                'key_id' => (string) config('services.api_signing.key_id', 'main-2026-01'),
            ],
        ];

        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json(
                $payload,
                422,
                ['X-Validation-Errors' => json_encode($errors)]
            )
        );
    }
}
