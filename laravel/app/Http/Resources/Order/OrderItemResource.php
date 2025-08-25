<?php

namespace App\Http\Resources\Order;

use App\Helpers\MoneyHelper;
use App\Http\Resources\Base\BaseResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Order Item data.
 * 
 * Handles the transformation of OrderItem model instances into standardized
 * JSON API responses. Includes product details, pricing information,
 * quantities, and related product data for order line items.
 *
 * @package App\Http\Resources\Order
 */
class OrderItemResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the order item resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'product_uuid' => $this->product_uuid,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'unit_price' => MoneyHelper::getAmountInfo($this->unit_price ?? 0),
            'total_price' => MoneyHelper::getAmountInfo($this->total_price ?? 0),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}