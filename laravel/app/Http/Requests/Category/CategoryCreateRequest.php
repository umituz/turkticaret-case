<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Request class for creating new categories.
 * 
 * Handles validation of category creation data including name uniqueness,
 * slug generation, and description validation. Automatically generates
 * category slugs from names during preparation.
 *
 * @package App\Http\Requests\Category
 */
class CategoryCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for authenticated users
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
            'name' => 'required|string|min:3|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'slug' => 'required|string|unique:categories,slug',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * Automatically generates a URL-friendly slug from the category name.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => isset($this->name) ? Str::slug($this->name) : null,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Array of custom error messages
     */
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