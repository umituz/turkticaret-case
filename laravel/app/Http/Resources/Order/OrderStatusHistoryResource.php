<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Order Status History data.
 * 
 * Handles the transformation of OrderStatusHistory model instances into standardized
 * JSON API responses. Includes status change tracking, timestamps, and audit trail
 * information for order status transitions.
 *
 * @package App\Http\Resources\Order
 */
class OrderStatusHistoryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the order status history resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'old_status' => $this->old_status?->value,
            'new_status' => $this->new_status->value,
            'notes' => $this->notes,
            'changed_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
