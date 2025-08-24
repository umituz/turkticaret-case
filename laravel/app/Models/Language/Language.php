<?php

namespace App\Models\Language;

use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Language Model for internationalization and localization.
 * 
 * Manages language information including ISO codes, names, locales, and
 * text direction. Provides language data for user preferences, content
 * localization, and internationalization throughout the application.
 *
 * @property string $uuid Language unique identifier
 * @property string $code ISO language code (e.g., 'en', 'fr', 'es')
 * @property string $name Language name in English (e.g., 'English', 'French')
 * @property string $native_name Language name in its native script (e.g., 'English', 'Français')
 * @property string $locale Full locale code (e.g., 'en_US', 'fr_FR', 'es_ES')
 * @property string $direction Text direction ('ltr' for left-to-right, 'rtl' for right-to-left)
 * @property bool $is_active Whether the language is available for selection
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Language
 */
class Language extends BaseUuidModel
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'locale',
        'direction',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all users that have this language set as their preference.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'language_uuid', 'uuid');
    }

    /**
     * Scope to filter only active languages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter languages by ISO code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code The ISO language code to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Check if this language uses right-to-left text direction.
     *
     * @return bool True if the language uses RTL direction, false otherwise
     */
    public function isRTL(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Check if this language uses left-to-right text direction.
     *
     * @return bool True if the language uses LTR direction, false otherwise
     */
    public function isLTR(): bool
    {
        return $this->direction === 'ltr';
    }
}
