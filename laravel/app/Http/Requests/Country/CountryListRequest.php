<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

class CountryListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active_only' => 'nullable|string|in:true,false',
        ];
    }

    public function messages(): array
    {
        return [
            'active_only.in' => 'Active only must be true or false.',
        ];
    }
}