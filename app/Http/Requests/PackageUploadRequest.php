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
            'file' => [
                'required',
                'file',
                'mimes:zip,tar,gz,rar,7z',
                'max:102400', // 100MB
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
            'file.required' => 'The package file is required.',
            'file.mimes' => 'The package file must be a valid archive (zip, tar, gz, rar, 7z).',
            'file.max' => 'The package file may not be greater than 100MB.',
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
