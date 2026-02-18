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
            'privilege' => [
                'required',
                'integer',
                Rule::in([0, 1, 2, 3, 6, 7]), // 0=none, 1=standard, 2=upgrade, 3=ultimate, 6=tester, 7=staff
            ],
            'status' => [
                'nullable',
                'integer',
                Rule::in([0, 1, 2, 3, 4, 5]), // 0=unused, 1=active, 2=suspended, 3=expired, 4=upgraded, 5=revoked
            ],
            'expires_at' => [
                'required',
                'date',
                'after:now',
            ],
            'used_by' => [
                'nullable',
                'integer',
                'exists:accounts,id',
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

        // For updates, key is required and must exist; expires_at may already be in the past
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['key'][] = 'exists:licenses,key';
            $rules['expires_at'] = ['required', 'date'];
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
            'privilege.required' => 'The license privilege is required.',
            'privilege.in' => 'The license privilege must be a valid value (0, 1, 2, 3, 6, or 7).',
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
