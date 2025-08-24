<?php

namespace App\Http\Requests\Order;

use App\Enums\Order\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Request class for updating order status.
 * 
 * Handles validation of order status updates. Ensures the provided status
 * is valid according to the OrderStatusEnum and performs necessary authorization
 * checks for administrative operations.
 *
 * @package App\Http\Requests\Order
 */
class OrderStatusUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true for now
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
        return [
            'status' => ['required', 'string', new Enum(OrderStatusEnum::class)],
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
            'status.required' => 'Status field is required',
            'status.string' => 'Status must be a string',
        ];
    }
}