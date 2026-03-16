<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateCheckRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_token' => ['nullable', 'string'],
            'release_channel' => ['nullable', 'string'],
            'current_version' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('session_token') && is_string($this->session_token)) {
            $this->merge([
                'session_token' => trim($this->session_token),
            ]);
        }

        if ($this->filled('release_channel') && is_string($this->release_channel)) {
            $this->merge([
                'release_channel' => strtolower(trim($this->release_channel)),
            ]);
        }

        if ($this->filled('current_version') && is_string($this->current_version)) {
            $this->merge([
                'current_version' => trim($this->current_version),
            ]);
        }
    }
}
