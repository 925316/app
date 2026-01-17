<?php

namespace App\Http\Requests;

use App\Services\LicenseService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenseRequest extends FormRequest
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
            'key' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (! LicenseService::validateLicenseKeyFormat($value)) {
                        $fail('The license key format is invalid. Must be XXXXX-XXXXX-XXXXX-XXXXX-XXXXX format.');
                    }
                },
            ],
            'type' => [
                'required',
                'integer',
                Rule::in([1, 2]), // 1=base, 2=upgrade
            ],
            'privilege' => [
                'required',
                'integer',
                Rule::in([0, 1, 2, 3, 4, 5]), // 0=none, 1=basic, 2=regular, 3=ultimate, 4=tester, 5=staff
            ],
            'status' => [
                'required',
                'integer',
                Rule::in([0, 1, 2, 3, 4, 5]), // 0=unused, 1=active, 2=suspended, 3=expired, 4=upgraded, 5=revoked
            ],
            'expires_at' => [
                'required',
                'date',
                'after:now',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:65535',
            ],
        ];

        // For creation, key can be optional (auto-generated)
        if ($this->isMethod('post')) {
            $rules['key'] = ['nullable', 'string', 'max:50'];
            if ($this->filled('key')) {
                $rules['key'][] = 'unique:licenses,key';
                $rules['key'][] = function ($attribute, $value, $fail) {
                    if (! LicenseService::validateLicenseKeyFormat($value)) {
                        $fail('The license key format is invalid.');
                    }
                };
            }
        }

        // For updates, key is required and must exist
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['key'][] = 'exists:licenses,key';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'The license key is required.',
            'key.unique' => 'This license key is already in use.',
            'key.exists' => 'The specified license key does not exist.',
            'type.required' => 'The license type is required.',
            'type.in' => 'The license type must be either base (1) or upgrade (2).',
            'privilege.required' => 'The license privilege is required.',
            'privilege.in' => 'The license privilege must be a valid value (0-5).',
            'status.required' => 'The license status is required.',
            'status.in' => 'The license status must be a valid value (0-5).',
            'expires_at.required' => 'The expiration date is required.',
            'expires_at.after' => 'The expiration date must be in the future.',
            'notes.max' => 'The notes field cannot exceed 65535 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('key') && is_string($this->key)) {
            $this->merge([
                'key' => strtoupper($this->key),
            ]);
        }
    }
}
