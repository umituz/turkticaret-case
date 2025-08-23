<?php

namespace App\Http\Requests\User\UserSettings;

use Illuminate\Foundation\Http\FormRequest;

class UserSettingsPasswordChangeRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'current_password.string' => 'Current password must be a valid string.',
            'new_password.required' => 'New password is required.',
            'new_password.string' => 'New password must be a valid string.',
            'new_password.min' => 'New password must be at least 8 characters long.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password_confirmation.required' => 'New password confirmation is required.',
            'new_password_confirmation.string' => 'New password confirmation must be a valid string.',
        ];
    }
}