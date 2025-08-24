<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Request class for updating existing categories.
 * 
 * Handles validation of category update data including name uniqueness
 * checks that exclude the current category, slug regeneration, and
 * optional field updates.
 *
 * @package App\Http\Requests\Category
 */
class CategoryUpdateRequest extends FormRequest
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
     * @return array<string, mixed> Array of validation rules
     */
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

    /**
     * Prepare the data for validation.
     * 
     * Automatically generates a new URL-friendly slug from the category name
     * if the name is being updated.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
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