<?php

namespace App\Http\Resources\Cart;

use App\Helpers\MoneyHelper;
use App\Http\Resources\Base\BaseResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Cart Item data.
 * 
 * Handles the transformation of CartItem model instances into standardized
 * JSON API responses. Includes product details, quantity information,
 * pricing data, and related product resources for shopping cart items.
 *
 * @package App\Http\Resources\Cart
 */
class CartItemResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the cart item resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'product_uuid' => $this->product_uuid,
            'quantity' => $this->quantity,
            'unit_price' => MoneyHelper::getAmountInfo($this->unit_price ?? 0),
            'total_price' => MoneyHelper::getAmountInfo($this->total_price ?? 0),
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}