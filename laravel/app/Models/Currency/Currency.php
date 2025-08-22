<?php

namespace App\Models\Currency;

use App\Models\Base\BaseUuidModel;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        ];
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'currency_uuid', 'uuid');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function formatAmount(float $amount): string
    {
        return number_format($amount, $this->decimals) . ' ' . $this->symbol;
    }
}
