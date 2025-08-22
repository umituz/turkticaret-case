<?php

namespace App\Models\Country;

use App\Models\Auth\User;
use App\Models\Base\BaseUuidModel;
use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends BaseUuidModel
{
    protected $fillable = [
        'code',
        'name',
        'currency_uuid',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_code', 'code');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_uuid', 'uuid');
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class, 'country_uuid', 'uuid');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function getDefaultCurrency()
    {
        return $this->currency;
    }
}
