<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $country = $this->route('country');
        
        return [
            'code' => ['sometimes', 'string', 'size:2', Rule::unique('countries', 'code')->ignore($country->uuid, 'uuid')],
            'name' => 'sometimes|string|max:255',
            'locale' => 'sometimes|string|max:10',
            'currency_uuid' => 'sometimes|nullable|string|exists:currencies,uuid',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.size' => 'Country code must be exactly 2 characters.',
            'code.unique' => 'This country code already exists.',
            'name.max' => 'Country name must not exceed 255 characters.',
            'currency_uuid.exists' => 'The selected currency is invalid.',
            'is_active.boolean' => 'Is active must be a boolean value.',
        ];
    }
}