<?php

namespace App\Models\Cart;

use App\Models\Base\BaseUuidModel;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends BaseUuidModel
{
    protected $fillable = [
        'cart_uuid',
        'product_uuid',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_uuid', 'uuid');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function getTotalPriceAttribute(): int
    {
        return $this->quantity * $this->unit_price;
    }
}
