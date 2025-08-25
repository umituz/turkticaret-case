<?php

namespace App\Http\Resources\Cart;

use App\Helpers\MoneyHelper;
use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Cart data.
 *
 * Handles the transformation of Cart model instances into standardized
 * JSON API responses. Includes cart summary, item collections,
 * total calculations, and user cart state for e-commerce functionality.
 *
 * @package App\Http\Resources\Cart
 */
class CartResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the cart resource
     */
    public function toArray(Request $request): array
    {
        $cartItems = $this->cartItems ?? collect([]);
        $cartItemsCollection = collect($cartItems);
        $totalAmount = $cartItemsCollection->sum('total_price');

        return [
            'uuid' => $this->uuid,
            'user_uuid' => $this->user_uuid,
            'items' => CartItemResource::collection($this->cartItems ?? []),
            'total_items' => $cartItemsCollection->sum('quantity'),
            'total_amount' => MoneyHelper::getAmountInfo($totalAmount),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
