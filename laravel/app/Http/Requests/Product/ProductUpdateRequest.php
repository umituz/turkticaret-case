<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for updating product.
 * 
 * Handles validation of product update data including optional field validation,
 * SKU uniqueness verification excluding current product, price and stock validation,
 * and category relationship validation for product modifications.
 *
 * @package App\Http\Requests\Product
 */
class ProductUpdateRequest extends FormRequest
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
            'name' => 'sometimes|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => 'sometimes|string|max:50|unique:products,sku,' . $this->product?->uuid . ',uuid',
            'price' => 'sometimes|integer|min:1',
            'stock_quantity' => 'sometimes|integer|min:0',
            'image_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'category_uuid' => 'sometimes|uuid|exists:categories,uuid',
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
            'name.min' => 'Product name must be at least 3 characters',
            'sku.unique' => 'SKU already exists',
            'price.min' => 'Price must be at least 1',
            'stock_quantity.min' => 'Stock quantity cannot be negative',
            'category_uuid.exists' => 'Selected category does not exist',
        ];
    }
}