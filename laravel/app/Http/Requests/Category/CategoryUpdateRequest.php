<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryUuid = $this->route('category')->uuid;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryUuid, 'uuid')
            ],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.min' => 'Category name must be at least 3 characters',
            'name.unique' => 'Category name already exists',
            'description.max' => 'Description cannot exceed 1000 characters',
        ];
    }
}