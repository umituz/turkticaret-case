<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

class OrderStatusHistoryResource extends BaseResource
{
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
