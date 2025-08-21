<?php

namespace App\Models\Product;

use App\Models\Base\BaseUuidModel;
use App\Models\Cart\CartItem;
use App\Models\Category\Category;
use App\Models\Order\OrderItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends BaseUuidModel
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'stock_quantity',
        'image_path',
        'is_active',
        'category_uuid',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_uuid', 'uuid');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_uuid', 'uuid');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_uuid', 'uuid');
    }
}
