<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

class CartResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'user_uuid' => $this->user_uuid,
            'items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'total_items' => $this->cartItems?->sum('quantity') ?? 0,
            'total_amount' => $this->cartItems?->sum('total_price') ?? 0,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}