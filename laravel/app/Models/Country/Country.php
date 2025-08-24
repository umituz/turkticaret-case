<?php

namespace App\Models\Country;

use App\Models\Base\BaseUuidModel;
use App\Models\Currency\Currency;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Country Model representing countries for internationalization.
 * 
 * Manages country information including ISO codes, names, locales, and
 * currency associations. Provides geographic and localization data
 * for user addresses, shipping, and regional settings.
 *
 * @property string $uuid Country unique identifier
 * @property string $code ISO country code (e.g., 'US', 'CA', 'UK')
 * @property string $name Country name in English
 * @property string $locale Country locale code (e.g., 'en_US', 'fr_CA')
 * @property string|null $currency_uuid Associated default currency UUID
 * @property bool $is_active Whether the country is available for selection
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Country
 */
class Country extends BaseUuidModel
{
    protected $fillable = [
        'code',
        'name',
        'locale',
        'currency_uuid',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all users associated with this country.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_code', 'code');
    }

    /**
     * Get the default currency for this country.
     *
     * @return BelongsTo<Currency>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_uuid', 'uuid');
    }

    /**
     * Get all currencies associated with this country.
     *
     * @return HasMany<Currency>
     */
    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class, 'country_uuid', 'uuid');
    }

    /**
     * Scope to filter only active countries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter countries by ISO code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code The ISO country code to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Get the default currency for this country.
     *
     * @return Currency|null The default currency instance or null
     */
    public function getDefaultCurrency()
    {
        return $this->currency;
    }
}
