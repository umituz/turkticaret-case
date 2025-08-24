<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating new orders.
 * 
 * Handles validation of order creation data including shipping address
 * validation and optional notes. Ensures all required fields are present
 * and valid before order processing.
 *
 * @package App\Http\Requests\Order
 */
class OrderCreateRequest extends FormRequest
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
            'shipping_address' => 'required|string|min:10|max:500',
            'notes' => 'nullable|string|max:1000',
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
            'shipping_address.required' => 'Shipping address is required',
            'shipping_address.min' => 'Shipping address must be at least 10 characters',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters',
            'notes.max' => 'Notes cannot exceed 1000 characters',
        ];
    }
}