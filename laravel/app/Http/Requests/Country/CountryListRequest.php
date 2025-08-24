<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing countries with filters.
 * 
 * Handles validation of country listing parameters including active status
 * filtering. Provides filtering options for country browsing in
 * geographical management interfaces.
 *
 * @package App\Http\Requests\Country
 */
class CountryListRequest extends FormRequest
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
            'active_only' => 'nullable|string|in:true,false',
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
            'active_only.in' => 'Active only must be true or false.',
        ];
    }
}