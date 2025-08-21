<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Base\BaseResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;

class CartItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'product_uuid' => $this->product_uuid,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}