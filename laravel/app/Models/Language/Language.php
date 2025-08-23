<?php

namespace App\Models\Language;

use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'language_uuid', 'uuid');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function isRTL(): bool
    {
        return $this->direction === 'rtl';
    }

    public function isLTR(): bool
    {
        return $this->direction === 'ltr';
    }
}
