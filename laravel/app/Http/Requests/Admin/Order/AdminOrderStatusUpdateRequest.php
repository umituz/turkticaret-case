<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for admin order status updates.
 *
 * Validates order status update requests from admin users,
 * ensuring proper status values and authorization.
 *
 * @package App\Http\Requests\Admin\Order
 */
class AdminOrderStatusUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Whether the user is authorized
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> Validation rules array
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Custom error messages
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Order status is required',
            'status.in' => 'Status must be one of: pending, processing, shipped, delivered, cancelled',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string> Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'status' => 'order status',
        ];
    }
}