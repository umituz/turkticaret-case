<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|uuid|exists:products,uuid',
            'quantity' => 'required|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'product_uuid.required' => 'Product is required',
            'product_uuid.exists' => 'Selected product does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'quantity.max' => 'Quantity cannot exceed 100',
        ];
    }
}