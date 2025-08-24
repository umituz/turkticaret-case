<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for updating cart items.
 * 
 * Handles validation of cart update data including product existence
 * validation and quantity modifications. Ensures proper cart item
 * updates with business rule compliance.
 *
 * @package App\Http\Requests\Cart
 */
class CartUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for authenticated users updating cart
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
            'product_uuid' => 'required|uuid|exists:products,uuid',
            'quantity' => 'required|integer|min:1|max:100',
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
            'product_uuid.required' => 'Product is required',
            'product_uuid.exists' => 'Selected product does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'quantity.max' => 'Quantity cannot exceed 100',
        ];
    }
}