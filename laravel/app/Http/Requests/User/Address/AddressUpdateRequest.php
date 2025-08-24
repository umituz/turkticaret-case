<?php

namespace App\Http\Requests\User\Address;

use Illuminate\Foundation\Http\FormRequest;

class AddressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

    public function messages(): array
    {
        return [
            'type.in' => 'Address type must be either shipping or billing.',
            'country_uuid.exists' => 'Selected country is invalid.',
        ];
    }
}