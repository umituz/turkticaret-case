<?php

namespace App\Http\Resources\Language;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * API Resource for transforming Language data.
 * 
 * Handles the transformation of Language model instances into standardized
 * JSON API responses. Includes language details, localization metadata,
 * and RTL support information for multilingual functionality.
 *
 * @package App\Http\Resources\Language
 */
class LanguageResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the language resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'native_name' => $this->native_name,
            'locale' => $this->locale,
            'direction' => $this->direction,
            'is_rtl' => $this->isRTL(),
            'is_active' => $this->is_active,
            'countries_count' => $this->whenLoaded('countries', fn() => $this->countries->count()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
