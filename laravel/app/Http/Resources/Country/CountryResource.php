<?php

namespace App\Http\Resources\Country;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Country data.
 * 
 * Handles the transformation of Country model instances into standardized
 * JSON API responses. Includes country codes, localization settings,
 * and currency relationship data for geographical operations.
 *
 * @package App\Http\Resources\Country
 */
class CountryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the country resource
     */
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
