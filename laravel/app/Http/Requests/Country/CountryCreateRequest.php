<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

class CountryCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|size:2|unique:countries,code',
            'name' => 'required|string|max:255',
            'currency_uuid' => 'nullable|string|exists:currencies,uuid',
            'is_active' => 'nullable|boolean',
        ];
    }

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