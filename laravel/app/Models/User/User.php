<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Cart\Cart;
use App\Models\Country\Country;
use App\Models\Language\Language;
use App\Models\Order\Order;
use Carbon\Carbon;
use Database\Factories\User\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model representing application users for the e-commerce system.
 *
 * Handles user authentication, authorization, profile management, and relationships
 * with core e-commerce entities including shopping carts, orders, and preferences.
 * Implements role-based access control and API token authentication.
 *
 * @property string $uuid User unique identifier
 * @property string $name Full name of the user
 * @property string $email User email address (unique, lowercase)
 * @property string $password Hashed password
 * @property string|null $email_verified_at Email verification timestamp
 * @property string|null $remember_token Remember token for authentication
 * @property string|null $language_uuid Associated language preference UUID
 * @property string|null $country_uuid Associated country UUID
 * @property string|null $timezone User timezone preference
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 * @property Carbon|null $deleted_at Soft deletion timestamp
 *
 * @package App\Models\User
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'language_uuid',
        'country_uuid',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Hash the user's password when setting it.
     *
     * @param string $value The plain text password to hash
     * @return void
     */
    protected function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Normalize the user's email address (lowercase and trimmed).
     *
     * @param string $value The email address to normalize
     * @return void
     */
    protected function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    /**
     * Get the user's shopping cart.
     *
     * @return HasOne<Cart>
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'user_uuid', 'uuid');
    }

    /**
     * Get all orders belonging to this user.
     *
     * @return HasMany<Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the user's language preference.
     *
     * @return BelongsTo<Language>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_uuid', 'uuid');
    }

    /**
     * Get the user's country.
     *
     * @return BelongsTo<Country>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_uuid', 'uuid');
    }

    /**
     * Get the user's settings configuration.
     *
     * @return HasOne<UserSetting>
     */
    public function userSettings(): HasOne
    {
        return $this->hasOne(UserSetting::class, 'user_uuid', 'uuid');
    }

    /**
     * Get all addresses associated with this user.
     *
     * @return HasMany<UserAddress>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'user_uuid', 'uuid');
    }

    /**
     * Scope to filter only active users (not soft deleted).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
