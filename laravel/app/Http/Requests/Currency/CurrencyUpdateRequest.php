<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currency = $this->route('currency');
        
        return [
            'code' => ['sometimes', 'string', 'size:3', Rule::unique('currencies', 'code')->ignore($currency->uuid, 'uuid')],
            'name' => 'sometimes|string|max:255',
            'symbol' => 'sometimes|string|max:10',
            'decimals' => 'sometimes|integer|min:0|max:4',
            'is_active' => 'sometimes|boolean',
        ];
    }
}