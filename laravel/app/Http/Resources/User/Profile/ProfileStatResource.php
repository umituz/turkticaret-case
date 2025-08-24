<?php

namespace App\Http\Resources\User\Profile;

use App\Http\Resources\Base\BaseResource;

class ProfileStatResource extends BaseResource
{
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