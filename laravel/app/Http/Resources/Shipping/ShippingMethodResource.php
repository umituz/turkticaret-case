<?php

namespace App\Http\Resources\Shipping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for transforming Shipping Method data.
 * 
 * Handles the transformation of ShippingMethod model instances into standardized
 * JSON API responses. Includes shipping pricing, delivery time estimates,
 * and availability status for checkout and order management.
 *
 * @package App\Http\Resources\Shipping
 */
class ShippingMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the shipping method resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'delivery_time' => $this->delivery_time,
            'min_delivery_days' => $this->min_delivery_days,
            'max_delivery_days' => $this->max_delivery_days,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}