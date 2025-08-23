<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\Base\BaseCollection;

class SettingCollection extends BaseCollection
{
    public $collects = SettingResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'groups' => $this->collection->groupBy('group')->keys(),
            ],
        ];
    }
}