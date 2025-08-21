<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

class OrderResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'order_number' => $this->order_number,
            'user_uuid' => $this->user_uuid,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
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