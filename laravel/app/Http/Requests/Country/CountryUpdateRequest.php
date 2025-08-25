<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating country.
 * 
 * Handles validation of country update data including country code uniqueness
 * verification excluding current country, locale validation, currency relationship
 * updates, and status management for country modifications.
 *
 * @package App\Http\Requests\Country
 */
class CountryUpdateRequest extends FormRequest
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
        $country = $this->route('country');
        
        return [
            'code' => ['sometimes', 'string', 'size:2', Rule::unique('countries', 'code')->ignore($country->uuid, 'uuid')],
            'name' => 'sometimes|string|max:255',
            'locale' => 'sometimes|string|max:10',
            'currency_uuid' => 'sometimes|required|string|exists:currencies,uuid',
            'is_active' => 'sometimes|boolean',
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
            'code.size' => 'Country code must be exactly 2 characters.',
            'code.unique' => 'This country code already exists.',
            'name.max' => 'Country name must not exceed 255 characters.',
            'locale.max' => 'Locale must not exceed 10 characters.',
            'currency_uuid.required' => 'Currency is required.',
            'currency_uuid.exists' => 'The selected currency is invalid.',
            'is_active.boolean' => 'Is active must be a boolean value.',
        ];
    }
}