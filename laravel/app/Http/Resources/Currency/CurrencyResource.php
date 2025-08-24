<?php

namespace App\Http\Resources\Currency;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Currency data.
 * 
 * Handles the transformation of Currency model instances into standardized
 * JSON API responses. Includes currency codes, symbols, decimal precision,
 * and country relationship data for financial operations.
 *
 * @package App\Http\Resources\Currency
 */
class CurrencyResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the currency resource
     */
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
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
