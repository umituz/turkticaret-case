<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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