<?php

namespace App\Http\Resources\Country;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

class CountryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'currency_uuid' => $this->currency_uuid,
            'is_active' => $this->is_active,
            'currency' => $this->whenLoaded('currency'),
            'users_count' => $this->whenLoaded('users', fn() => $this->users->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
