<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
        $isAdmin = $this->user()->hasPrivilege(7);
        $supportedLocales = array_keys((array) config('app.supported_locales', []));
        $localeRules = empty($supportedLocales)
            ? ['nullable', 'string']
            : ['nullable', 'string', Rule::in($supportedLocales)];

        // Only admin can update username and email
        if (! $isAdmin) {
            return [
                'locale' => $localeRules,
            ];
        }

        return [
            'username' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(Account::class)->ignore($this->user()->id),
            ],
            'locale' => $localeRules,
        ];
    }
}
