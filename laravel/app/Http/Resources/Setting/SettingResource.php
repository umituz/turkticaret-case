<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\Base\BaseResource;

class SettingResource extends BaseResource
{
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