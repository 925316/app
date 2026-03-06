<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientLoginRequest extends FormRequest
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
            'email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'hwid' => ['required', 'string', 'min:8', 'max:255'],
            'nonce' => ['required', 'string', 'min:8', 'max:128'],
            'timestamp' => ['required', 'integer', 'min:0'],
            'version' => ['required', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'size:2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hwid.required' => 'HWID is required.',
            'nonce.required' => 'Nonce is required.',
            'timestamp.required' => 'Timestamp is required.',
            'timestamp.integer' => 'Timestamp must be an integer Unix timestamp.',
            'version.required' => 'Version is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email') && is_string($this->email)) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }

        if ($this->filled('country_code') && is_string($this->country_code)) {
            $this->merge([
                'country_code' => strtoupper($this->country_code),
            ]);
        }
    }
}
