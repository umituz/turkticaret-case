<?php

namespace App\Http\Requests\Admin\Order;

use App\Enums\Order\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Form request for admin order listing with filters.
 *
 * Validates filter parameters for admin order queries including
 * status, user UUID, order number, date ranges, and pagination.
 *
 * @package App\Http\Requests\Admin\Order
 */
class AdminOrderListRequest extends FormRequest
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
            'status' => ['sometimes', 'string', new Enum(OrderStatusEnum::class)],
            'user_uuid' => 'sometimes|string|uuid',
            'order_number' => 'sometimes|string|max:50',
            'date_from' => 'sometimes|date|date_format:Y-m-d',
            'date_to' => 'sometimes|date|date_format:Y-m-d|after_or_equal:date_from',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Custom error messages
     */
    public function messages(): array
    {
        $availableStatuses = implode(', ', OrderStatusEnum::getAvailableStatuses());
        
        return [
            'status.Illuminate\Validation\Rules\Enum' => "Status must be one of: {$availableStatuses}",
            'user_uuid.uuid' => 'User UUID must be a valid UUID format',
            'date_from.date_format' => 'Date from must be in Y-m-d format',
            'date_to.date_format' => 'Date to must be in Y-m-d format',
            'date_to.after_or_equal' => 'Date to must be after or equal to date from',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page cannot exceed 100',
        ];
    }

    /**
     * Get validated filters for the order query.
     *
     * @return array<string, mixed> Validated filter parameters
     */
    public function filters(): array
    {
        return $this->only([
            'status',
            'user_uuid', 
            'order_number',
            'date_from',
            'date_to',
            'per_page'
        ]);
    }
}