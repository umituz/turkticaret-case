<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating new languages.
 * 
 * Handles validation of language creation data including language code
 * uniqueness, name and native name validation, locale settings, and
 * text direction configuration for internationalization support.
 *
 * @package App\Http\Requests\Language
 */
class LanguageCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for authorized administrators
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
            'code' => 'required|string|max:5|unique:languages,code',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'direction' => 'nullable|string|in:ltr,rtl',
            'is_active' => 'nullable|boolean',
        ];
    }
}