<?php

namespace App\Http\Resources\User\Profile;

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
        return [
            'total_orders' => $this->resource['total_orders'],
            'total_spent' => $this->resource['total_spent'],
            'average_order_value' => $this->resource['average_order_value'],
            'member_since' => $this->resource['member_since'],
            'last_order' => $this->resource['last_order'],
        ];
    }
}