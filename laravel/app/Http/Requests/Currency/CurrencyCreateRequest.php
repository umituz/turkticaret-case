<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating currency.
 * 
 * Handles validation of currency creation data including code uniqueness,
 * symbol validation, decimal precision constraints, and activation status
 * for new currency registration in the system.
 *
 * @package App\Http\Requests\Currency
 */
class CurrencyCreateRequest extends FormRequest
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
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'decimals' => 'nullable|integer|min:0|max:4',
            'is_active' => 'nullable|boolean',
        ];
    }
}