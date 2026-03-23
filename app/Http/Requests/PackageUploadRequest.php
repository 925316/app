<?php

namespace App\Http\Requests;

use App\Services\PackageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageUploadRequest extends FormRequest
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
        return [
            'version' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (! PackageService::isValidSemanticVersion($value)) {
                        $fail('The version must follow semantic versioning format (e.g., 1.0.0).');
                    }
                },
                'unique:package_releases,version',
            ],
            'release_channel' => [
                'required',
                'string',
                Rule::in(['stable', 'dev']),
            ],
            'download_url' => [
                'required',
                'url',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (! is_string($value) || ! PackageService::isSafePublicHttpsUrl($value)) {
                        $fail('The download URL must be a valid public HTTPS URL.');
                    }
                },
            ],
            'virus_detection_url' => [
                'nullable',
                'url',
                'max:2000',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! is_string($value) || ! PackageService::isSafePublicHttpsUrl($value)) {
                        $fail('The virus detection URL must be a valid public HTTPS URL.');
                    }
                },
            ],
            'changelog' => [
                'nullable',
                'string',
                'max:65535',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'version.required' => 'The version is required.',
            'version.unique' => 'This version already exists.',
            'release_channel.required' => 'The release channel is required.',
            'release_channel.in' => 'The release channel must be either stable or dev.',
            'download_url.required' => 'The download URL is required.',
            'download_url.url' => 'The download URL must be a valid URL.',
            'download_url.max' => 'The download URL may not be greater than 255 characters.',
            'changelog.max' => 'The changelog cannot exceed 65535 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('version') && is_string($this->version)) {
            $this->merge([
                'version' => trim($this->version),
            ]);
        }
    }
}
