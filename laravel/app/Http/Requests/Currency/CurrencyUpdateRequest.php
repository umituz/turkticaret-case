<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating currency.
 * 
 * Handles validation of currency update data including code uniqueness
 * verification excluding current currency, symbol validation, decimal precision
 * constraints, and status management for currency modifications.
 *
 * @package App\Http\Requests\Currency
 */
class CurrencyUpdateRequest extends FormRequest
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