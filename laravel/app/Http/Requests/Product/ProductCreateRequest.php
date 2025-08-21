<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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