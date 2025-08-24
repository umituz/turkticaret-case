<?php

namespace App\Models\User;

use App\Models\Base\BaseUuidModel;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserAddress Model representing user addresses for shipping and billing.
 * 
 * Manages user address information including contact details, location data,
 * and address types. Supports default address marking and provides
 * comprehensive address management for e-commerce operations.
 *
 * @property string $uuid Address unique identifier
 * @property string $user_uuid Associated user UUID
 * @property string $type Address type (billing, shipping, etc.)
 * @property bool $is_default Whether this is the user's default address
 * @property string $first_name First name for the address
 * @property string $last_name Last name for the address
 * @property string|null $company Company name (optional)
 * @property string $address_line_1 Primary address line
 * @property string|null $address_line_2 Secondary address line (optional)
 * @property string $city City name
 * @property string $state State or province
 * @property string $postal_code Postal or ZIP code
 * @property string $country_uuid Associated country UUID
 * @property string|null $phone Phone number (optional)
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\User
 */
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

    /**
     * Get the user that owns this address.
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the country associated with this address.
     *
     * @return BelongsTo<Country>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_uuid', 'uuid');
    }

    /**
     * Scope to filter only default addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to filter addresses by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type The address type to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}