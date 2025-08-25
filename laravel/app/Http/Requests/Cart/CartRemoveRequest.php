<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for cart item removal validation.
 *
 * Validates the product UUID parameter to ensure it's a valid UUID format
 * before attempting to remove the item from the cart.
 *
 * @package App\Http\Requests\Cart
 */
class CartRemoveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_uuid' => ['required', 'string', 'uuid', 'exists:products,uuid'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_uuid.required' => 'Product UUID is required.',
            'product_uuid.uuid' => 'Product UUID must be a valid UUID format.',
            'product_uuid.exists' => 'The selected product does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_uuid' => $this->route('product_uuid'),
        ]);
    }
}
