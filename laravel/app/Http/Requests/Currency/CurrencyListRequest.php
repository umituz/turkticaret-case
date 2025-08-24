<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing currencies with filters.
 * 
 * Handles validation of currency listing parameters including active status
 * filtering. Provides filtering options for currency browsing in
 * administrative and public contexts.
 *
 * @package App\Http\Requests\Currency
 */
class CurrencyListRequest extends FormRequest
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
}