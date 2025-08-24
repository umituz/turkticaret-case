<?php

namespace App\Models\Currency;

use App\Models\Base\BaseUuidModel;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Currency Model for multi-currency system support.
 * 
 * Manages currency information including ISO codes, names, symbols, and
 * decimal precision. Provides currency data for pricing, localization,
 * and financial calculations throughout the e-commerce system.
 *
 * @property string $uuid Currency unique identifier
 * @property string $code ISO currency code (e.g., 'USD', 'EUR', 'GBP')
 * @property string $name Currency name in English (e.g., 'US Dollar')
 * @property string $symbol Currency symbol (e.g., '$', '€', '£')
 * @property int $decimals Number of decimal places for this currency
 * @property bool $is_active Whether the currency is available for use
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Currency
 */
class Currency extends BaseUuidModel
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimals',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'decimals' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get all countries that use this currency.
     *
     * @return HasMany<Country>
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'currency_uuid', 'uuid');
    }

    /**
     * Scope to filter only active currencies.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter currencies by ISO code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code The ISO currency code to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

}
