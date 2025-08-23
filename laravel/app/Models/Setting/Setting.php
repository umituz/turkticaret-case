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
        $value = is_array($this->value) ? ($this->value['value'] ?? $this->value) : $this->value;
        
        // Handle JSON-encoded strings stored in database
        if (is_string($value) && $this->type === 'string' && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = json_decode($value);
        }
        
        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'string' => (string) $value,
            'json' => is_array($this->value) ? $this->value : json_decode($value, true),
            default => $value
        };
    }
}
