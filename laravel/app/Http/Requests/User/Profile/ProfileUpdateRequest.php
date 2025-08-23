<?php

namespace App\Http\Requests\User\Profile;

use App\Rules\Profile\AtLeastOneFieldRule;
use App\Rules\Profile\OldPasswordRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                new AtLeastOneFieldRule(['name', 'email', 'new_password']),
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->uuid, 'uuid'),
            ],
            'old_password' => [
                'required_with:new_password',
                'string',
                new OldPasswordRule(),
            ],
            'new_password' => 'sometimes|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required_with' => 'Old password is required when changing password.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.min' => 'The new password must be at least 8 characters.',
            'email.unique' => 'This email address is already taken.',
        ];
    }
}
