<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing languages with filters.
 * 
 * Handles validation of language listing parameters including
 * active status filtering. Provides flexible querying options
 * for language management interfaces.
 *
 * @package App\Http\Requests\Language
 */
class LanguageListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for public language listing
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