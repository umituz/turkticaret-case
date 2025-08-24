<?php

namespace App\Models\Setting;

use App\Models\Base\BaseUuidModel;

/**
 * Setting Model for application configuration management.
 * 
 * Manages application-wide settings with type-aware value handling, grouping,
 * and editability controls. Supports various data types including boolean,
 * integer, string, and JSON values with automatic type casting.
 *
 * @property string $uuid Setting unique identifier
 * @property string $key Setting key (unique identifier for the setting)
 * @property mixed $value Setting value (stored as array, cast based on type)
 * @property string $type Value type ('boolean', 'integer', 'string', 'json')
 * @property string $group Setting group for organization
 * @property string|null $description Human-readable description of the setting
 * @property bool $is_active Whether the setting is active and should be used
 * @property bool $is_editable Whether the setting can be modified through UI
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Setting
 */
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

    /**
     * Scope to filter only active settings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter settings by group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $group The setting group to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter settings by key.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key The setting key to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get the setting value as a string representation.
     *
     * @return string The value converted to string format
     */
    public function getValueAsStringAttribute(): string
    {
        return match ($this->type) {
            'boolean' => $this->value['value'] ? 'true' : 'false',
            'json' => json_encode($this->value),
            default => (string) ($this->value['value'] ?? $this->value)
        };
    }

    /**
     * Get the setting value cast to its proper type.
     *
     * @return mixed The value cast to the appropriate type based on the type field
     */
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
