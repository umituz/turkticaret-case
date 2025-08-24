<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating country.
 * 
 * Handles validation of country creation data including country code uniqueness,
 * locale validation, currency relationship verification, and activation status
 * for new country registration in the geographical system.
 *
 * @package App\Http\Requests\Country
 */
class CountryCreateRequest extends FormRequest
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
     * @return array<string, string> Array of validation rules
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|size:2|unique:countries,code',
            'name' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'currency_uuid' => 'nullable|string|exists:currencies,uuid',
            'is_active' => 'nullable|boolean',
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
            'code.required' => 'Country code is required.',
            'code.size' => 'Country code must be exactly 2 characters.',
            'code.unique' => 'This country code already exists.',
            'name.required' => 'Country name is required.',
            'name.max' => 'Country name must not exceed 255 characters.',
            'currency_uuid.exists' => 'The selected currency is invalid.',
            'is_active.boolean' => 'Is active must be a boolean value.',
        ];
    }
}