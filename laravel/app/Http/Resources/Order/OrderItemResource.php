<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;

class OrderItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'product_uuid' => $this->product_uuid,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}