<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating existing languages.
 * 
 * Handles validation of language update data including language code
 * uniqueness checks that exclude the current language, and optional
 * field updates for internationalization management.
 *
 * @package App\Http\Requests\Language
 */
class LanguageUpdateRequest extends FormRequest
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
     * @return array<string, mixed> Array of validation rules
     */
    public function rules(): array
    {
        $language = $this->route('language');
        
        return [
            'code' => ['sometimes', 'string', 'max:5', Rule::unique('languages', 'code')->ignore($language->uuid, 'uuid')],
            'name' => 'sometimes|string|max:255',
            'native_name' => 'sometimes|string|max:255',
            'locale' => 'sometimes|string|max:10',
            'direction' => 'sometimes|string|in:ltr,rtl',
            'is_active' => 'sometimes|boolean',
        ];
    }
}