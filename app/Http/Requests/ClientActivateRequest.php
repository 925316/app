<?php

namespace App\Http\Requests;

use App\Services\LicenseService;
use Illuminate\Foundation\Http\FormRequest;

class ClientActivateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_token' => ['required', 'string', 'max:128'],
            'license_key' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! LicenseService::validateLicenseKeyFormat($value)) {
                        $fail('The license key format is invalid.');
                    }
                },
            ],
            'hwid' => ['required', 'string', 'min:8', 'max:255'],
            'nonce' => ['required', 'string', 'min:8', 'max:128'],
            'timestamp' => ['required', 'integer', 'min:0'],
            'version' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'session_token.required' => 'Session token is required.',
            'license_key.required' => 'License key is required.',
            'hwid.required' => 'HWID is required.',
            'nonce.required' => 'Nonce is required.',
            'timestamp.required' => 'Timestamp is required.',
            'timestamp.integer' => 'Timestamp must be an integer Unix timestamp.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('session_token') && is_string($this->session_token)) {
            $this->merge([
                'session_token' => trim($this->session_token),
            ]);
        }

        if ($this->filled('license_key') && is_string($this->license_key)) {
            $this->merge([
                'license_key' => strtoupper($this->license_key),
            ]);
        }

        if ($this->filled('hwid') && is_string($this->hwid)) {
            $this->merge([
                'hwid' => trim($this->hwid),
            ]);
        }

        if ($this->filled('version') && is_string($this->version)) {
            $this->merge([
                'version' => trim($this->version),
            ]);
        }
    }
}
