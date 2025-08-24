<?php

namespace App\Http\Requests\User\Profile;

use App\Rules\Profile\AtLeastOneFieldRule;
use App\Rules\Profile\OldPasswordRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating user profiles.
 * 
 * Handles validation of profile update data including name and email changes,
 * password updates with old password verification, and ensures at least one
 * field is provided for update operations.
 *
 * @package App\Http\Requests\User\Profile
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for authenticated users updating their own profile
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> Array of validation rules
     */
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

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Array of custom error messages
     */
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
