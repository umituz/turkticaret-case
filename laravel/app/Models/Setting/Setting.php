<?php

namespace App\Models\Setting;

use App\Models\Base\BaseUuidModel;

class Setting extends BaseUuidModel
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_active',
        'is_editable',
    ];

    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean',
        'is_editable' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function getValueAsStringAttribute(): string
    {
        return match ($this->type) {
            'boolean' => $this->value['value'] ? 'true' : 'false',
            'json' => json_encode($this->value),
            default => (string) ($this->value['value'] ?? $this->value)
        };
    }

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value['value'],
            'integer' => (int) $this->value['value'],
            'string' => (string) $this->value['value'],
            'json' => $this->value,
            default => $this->value['value']
        };
    }
}
