<?php

namespace App\Http\Requests\User\Address;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating user address.
 * 
 * Handles validation of address creation data including required fields validation,
 * address type verification, geographical information validation, and business
 * rule compliance for shipping and billing address creation.
 *
 * @package App\Http\Requests\User\Address
 */
class AddressCreateRequest extends FormRequest
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
            'type' => 'required|string|in:shipping,billing',
            'is_default' => 'boolean',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country_uuid' => 'required|uuid|exists:countries,uuid',
            'phone' => 'nullable|string|max:20',
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
            'type.required' => 'Address type is required.',
            'type.in' => 'Address type must be either shipping or billing.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'address_line_1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postal_code.required' => 'Postal code is required.',
            'country_uuid.required' => 'Country is required.',
            'country_uuid.exists' => 'Selected country is invalid.',
        ];
    }
}