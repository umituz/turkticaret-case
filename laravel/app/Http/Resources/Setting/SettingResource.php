<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\Base\BaseResource;

/**
 * API Resource for transforming Setting data.
 * 
 * Handles the transformation of Setting model instances into standardized
 * JSON API responses. Includes typed value conversion, configuration grouping,
 * and editability status for application settings management.
 *
 * @package App\Http\Resources\Setting
 */
class SettingResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $request The HTTP request instance
     * @return array<string, mixed> Array representation of the setting resource
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'key' => $this->key,
            'value' => $this->typed_value,
            'raw_value' => $this->value,
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_editable' => $this->is_editable,
            'value_as_string' => $this->value_as_string,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}