<?php

namespace App\Models\User;

use App\Models\Base\BaseUuidModel;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends BaseUuidModel
{
    protected $fillable = [
        'user_uuid',
        'type',
        'is_default',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_uuid',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_uuid', 'uuid');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}