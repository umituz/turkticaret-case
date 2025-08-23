<?php

namespace App\Http\Requests\Order;

use App\Enums\Order\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', new Enum(OrderStatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status field is required',
            'status.string' => 'Status must be a string',
        ];
    }
}