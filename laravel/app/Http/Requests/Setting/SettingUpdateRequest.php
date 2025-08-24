<?php

namespace App\Http\Requests\Setting;

use App\Enums\Setting\SettingKeyEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating application settings.
 * 
 * Handles validation of setting update data including key validation
 * against allowed SettingKeyEnum values, and dynamic value validation.
 * Ensures secure configuration management for application settings.
 *
 * @package App\Http\Requests\Setting
 */
class SettingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Authorization status
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
            'key' => [
                'required',
                'string',
                Rule::in(array_column(SettingKeyEnum::cases(), 'value')),
            ],
            'value' => 'required',
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
            'key.required' => 'Setting key is required.',
            'key.in' => 'Invalid setting key provided.',
            'value.required' => 'Setting value is required.',
        ];
    }
}