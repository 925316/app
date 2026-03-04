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
            'hwid' => [
                'required',
                'string',
                'min:8',
                'max:255',
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
            $rules['hwid'][] = function ($attribute, $value, $fail) {
                $account = $this->user();
                $incomingHwidHash = hash('sha256', (string) $value);

                // Check if device is already bound to this account
                if ($account->devices()->where('hwid_hash', $incomingHwidHash)
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
            'hwid.required' => 'The HWID is required.',
            'hwid.min' => 'The HWID must be at least 8 characters.',
            'hwid.max' => 'The HWID must be at most 255 characters.',
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
        if ($this->filled('hwid') && is_string($this->hwid)) {
            $this->merge([
                'hwid' => trim($this->hwid),
            ]);
        }

        if ($this->filled('country_code') && is_string($this->country_code)) {
            $this->merge([
                'country_code' => strtoupper($this->country_code),
            ]);
        }
    }
}
