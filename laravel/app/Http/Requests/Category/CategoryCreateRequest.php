<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CategoryCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'slug' => 'required|string|unique:categories,slug',
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => isset($this->name) ? Str::slug($this->name) : null,
        ]);
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