<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'hwid_hash' => [
                'required',
                'string',
                'size:64', // SHA-256 hash
                function ($attribute, $value, $fail) {
                    if (! ctype_xdigit($value)) {
                        $fail('The HWID hash must be a valid hexadecimal string.');
                    }
                },
            ],
            'ip_address' => [
                'required',
                'ip',
            ],
            'country_code' => [
                'nullable',
                'string',
                'size:2',
                'alpha',
            ],
        ];

        // For binding operations
        if ($this->routeIs('devices.bind')) {
            $rules['hwid_hash'][] = function ($attribute, $value, $fail) {
                $account = $this->user();

                // Check if device is already bound to this account
                if ($account->devices()->where('hwid_hash', $value)
                    ->whereNotNull('bound_at')
                    ->whereNull('unbound_at')
                    ->exists()) {
                    $fail('This device is already bound to your account.');
                }

                // Check if account already has a bound device
                if ($account->getBoundDeviceCount() >= 1) {
                    $fail('You can only bind one device at a time. Please unbind your current device first.');
                }
            };
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hwid_hash.required' => 'The HWID hash is required.',
            'hwid_hash.size' => 'The HWID hash must be 64 characters long.',
            'hwid_hash.regex' => 'The HWID hash must be a valid hexadecimal string.',
            'ip_address.required' => 'The IP address is required.',
            'ip_address.ip' => 'The IP address must be a valid IP address.',
            'country_code.size' => 'The country code must be 2 characters.',
            'country_code.alpha' => 'The country code must contain only letters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('hwid_hash') && is_string($this->hwid_hash)) {
            $this->merge([
                'hwid_hash' => strtolower($this->hwid_hash),
            ]);
        }

        if ($this->filled('country_code') && is_string($this->country_code)) {
            $this->merge([
                'country_code' => strtoupper($this->country_code),
            ]);
        }
    }
}
