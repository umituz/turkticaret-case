<?php

namespace App\Http\Resources\User\Profile;

use App\Helpers\MoneyHelper;
use App\Http\Resources\Base\BaseResource;

/**
 * API Resource for transforming User Profile Statistics data.
 * 
 * Handles the transformation of user profile statistics into standardized
 * JSON API responses. Includes order analytics, spending patterns,
 * and membership information for user dashboard displays.
 *
 * @package App\Http\Resources\User\Profile
 */
class ProfileStatResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $request The HTTP request instance
     * @return array<string, mixed> Array representation of the profile statistics resource
     */
    public function toArray($request): array
    {
        $lastOrder = $this->resource['last_order'];
        if ($lastOrder && isset($lastOrder['total'])) {
            $lastOrder['total'] = MoneyHelper::getAmountInfo($lastOrder['total'] ?? 0);
        }

        return [
            'total_orders' => $this->resource['total_orders'],
            'total_spent' => MoneyHelper::getAmountInfo($this->resource['total_spent'] ?? 0),
            'average_order_value' => MoneyHelper::getAmountInfo($this->resource['average_order_value'] ?? 0),
            'member_since' => $this->resource['member_since'],
            'last_order' => $lastOrder,
        ];
    }
}