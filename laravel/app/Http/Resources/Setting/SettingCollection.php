<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\Base\BaseCollection;

class SettingCollection extends BaseCollection
{
    public $collects = SettingResource::class;

    public function toArray($request): array
    {
        $baseArray = parent::toArray($request);
        
        // Add custom groups to meta using the original resource
        if ($this->resource && method_exists($this->resource, 'groupBy')) {
            $baseArray['meta']['groups'] = $this->resource->groupBy('group')->keys();
        } else {
            // Fallback for empty or non-collection data
            $baseArray['meta']['groups'] = collect();
        }
        
        return $baseArray;
    }
}