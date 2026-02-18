<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
        $accountId = $this->route('account')?->id;

        return [
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/', // Only letters, numbers, and underscores
                $accountId ? 'unique:accounts,username,'.$accountId : 'unique:accounts,username',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $accountId ? 'unique:accounts,email,'.$accountId : 'unique:accounts,email',
            ],
            'password' => [
                $accountId ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // At least one lowercase, one uppercase, and one number
            ],
            'email_verified' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, and one number.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
