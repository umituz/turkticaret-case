<?php

namespace App\Models\Cart;

use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends BaseUuidModel
{
    protected $fillable = [
        'user_uuid',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_uuid', 'uuid');
    }

    public function getTotalAmountAttribute(): int
    {
        return $this->cartItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function getItemCountAttribute(): int
    {
        return $this->cartItems->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->cartItems()->count() === 0;
    }
}
