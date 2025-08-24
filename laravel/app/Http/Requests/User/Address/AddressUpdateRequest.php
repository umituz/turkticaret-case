<?php

namespace App\Http\Requests\User\Address;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for updating user address.
 * 
 * Handles validation of address update data including address type validation,
 * geographical information verification, and optional field handling.
 * Ensures data integrity for shipping and billing address modifications.
 *
 * @package App\Http\Requests\User\Address
 */
class AddressUpdateRequest extends FormRequest
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
            'type' => 'sometimes|string|in:shipping,billing',
            'is_default' => 'sometimes|boolean',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'sometimes|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:20',
            'country_uuid' => 'sometimes|uuid|exists:countries,uuid',
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
            'type.in' => 'Address type must be either shipping or billing.',
            'country_uuid.exists' => 'Selected country is invalid.',
        ];
    }
}