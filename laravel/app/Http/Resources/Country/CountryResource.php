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
            'locale' => $this->locale,
            'is_active' => $this->is_active,
            'currency' => $this->whenLoaded('currency'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
