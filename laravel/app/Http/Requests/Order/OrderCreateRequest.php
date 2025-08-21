<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'required|string|min:10|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

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