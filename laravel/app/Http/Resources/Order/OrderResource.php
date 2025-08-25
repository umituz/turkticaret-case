<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseResource;
use App\Helpers\MoneyHelper;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Order data.
 * 
 * Handles the transformation of Order model instances into standardized
 * JSON API responses. Includes order details, status information, items,
 * timestamps, and security filtering for public API consumption.
 *
 * @package App\Http\Resources\Order
 */
class OrderResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the order resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'order_number' => $this->order_number,
            'user_uuid' => $this->user_uuid,
            'status' => $this->status?->value ?? 'pending',
            'total_amount' => MoneyHelper::getAmountInfo($this->total_amount ?? 0),
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'items_count' => $this->orderItems?->count() ?? 0,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}