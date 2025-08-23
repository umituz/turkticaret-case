<?php

namespace App\Models\Product;

use App\Models\Base\BaseUuidModel;
use App\Models\Cart\CartItem;
use App\Models\Category\Category;
use App\Models\Order\OrderItem;
use App\Traits\Filterable;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends BaseUuidModel implements HasMedia
{
    use Filterable, InteractsWithMedia, HasSlug;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'sku',
        'price',
        'stock_quantity',
        'is_active',
        'is_featured',
        'category_uuid',
    ];

    public array $searchableFields = ['name', 'description', 'sku'];
    public array $sortableFields = ['name', 'price', 'stock_quantity', 'created_at', 'updated_at'];

    protected $casts = [
        'price' => 'integer',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
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

    public function hasStock(int $requestedQuantity = 1): bool
    {
        return $this->stock_quantity >= $requestedQuantity;
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }


    public function decreaseStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        return $this->decrement('stock_quantity', $quantity);
    }

    public function increaseStock(int $quantity): bool
    {
        return $this->increment('stock_quantity', $quantity);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }
}
