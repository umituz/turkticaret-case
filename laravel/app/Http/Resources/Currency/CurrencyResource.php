<?php

namespace App\Http\Resources\Currency;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

class CurrencyResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'decimals' => $this->decimals,
            'is_active' => $this->is_active,
            'countries_count' => $this->whenLoaded('countries', fn() => $this->countries->count()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
