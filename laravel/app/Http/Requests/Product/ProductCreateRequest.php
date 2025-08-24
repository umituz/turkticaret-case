<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating product.
 * 
 * Handles validation of product creation data including name validation,
 * SKU uniqueness verification, price and stock validation, and category
 * relationship validation for new product registration.
 *
 * @package App\Http\Requests\Product
 */
class ProductCreateRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => 'required|string|max:50|unique:products,sku',
            'price' => 'required|integer|min:1',
            'stock_quantity' => 'required|integer|min:0',
            'image_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'category_uuid' => 'required|uuid|exists:categories,uuid',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Array of custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'name.min' => 'Product name must be at least 3 characters',
            'sku.required' => 'SKU is required',
            'sku.unique' => 'SKU already exists',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be at least 1',
            'stock_quantity.required' => 'Stock quantity is required',
            'stock_quantity.min' => 'Stock quantity cannot be negative',
            'category_uuid.required' => 'Category is required',
            'category_uuid.exists' => 'Selected category does not exist',
        ];
    }
}