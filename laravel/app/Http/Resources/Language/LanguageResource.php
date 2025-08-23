<?php

namespace App\Http\Resources\Language;

use App\Http\Resources\Base\BaseResource;
use Illuminate\Http\Request;

class LanguageResource extends BaseResource
{
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
