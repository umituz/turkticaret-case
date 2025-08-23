<?php

namespace App\Http\Requests\User\UserSettings;

use Illuminate\Foundation\Http\FormRequest;

class UserSettingsPreferencesUpdateRequest extends FormRequest
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
            'language_uuid' => 'required|uuid|exists:languages,uuid',
            'timezone' => 'required|string|max:255|timezone',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'language_uuid.required' => 'Language selection is required.',
            'language_uuid.uuid' => 'Language selection must be a valid UUID.',
            'language_uuid.exists' => 'Selected language is not available.',
            'timezone.required' => 'Timezone selection is required.',
            'timezone.string' => 'Timezone must be a valid string.',
            'timezone.max' => 'Timezone name is too long.',
            'timezone.timezone' => 'Timezone must be a valid timezone identifier.',
        ];
    }
}