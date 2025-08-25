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
use App\Traits\HasMoneyAttributes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Product Model representing e-commerce products.
 * 
 * Handles product information including inventory management, pricing,
 * categorization, media attachments, and stock operations. Implements
 * filterable and slug functionality for enhanced user experience.
 *
 * @property string $uuid Product unique identifier
 * @property string $name Product name
 * @property string $description Product description
 * @property string $slug URL-friendly product slug
 * @property string $sku Stock Keeping Unit code
 * @property int $price Product price in cents
 * @property int $stock_quantity Available stock quantity
 * @property bool $is_active Whether the product is active
 * @property bool $is_featured Whether the product is featured
 * @property string $category_uuid Associated category UUID
 * 
 * @package App\Models\Product
 */
class Product extends BaseUuidModel implements HasMedia
{
    use Filterable, InteractsWithMedia, HasSlug, HasMoneyAttributes;

    protected $fillable = [
        'name',
        'description',
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

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_uuid', 'uuid');
    }

    /**
     * Get all cart items containing this product.
     *
     * @return HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_uuid', 'uuid');
    }

    /**
     * Get all order items containing this product.
     *
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_uuid', 'uuid');
    }

    /**
     * Check if product has sufficient stock for requested quantity.
     *
     * @param int $requestedQuantity The quantity to check availability for
     * @return bool True if stock is sufficient, false otherwise
     */
    public function hasStock(int $requestedQuantity = 1): bool
    {
        return $this->stock_quantity >= $requestedQuantity;
    }

    /**
     * Check if product is in stock (has any available quantity).
     *
     * @return bool True if product has stock, false if out of stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }


    /**
     * Decrease product stock by specified quantity.
     *
     * @param int $quantity The quantity to decrease
     * @return bool True if stock was decreased successfully, false if insufficient stock
     */
    public function decreaseStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }

        return $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Increase product stock by specified quantity.
     *
     * @param int $quantity The quantity to increase
     * @return bool True if stock was increased successfully
     */
    public function increaseStock(int $quantity): bool
    {
        return $this->increment('stock_quantity', $quantity);
    }

    /**
     * Register media collections for product images.
     *
     * @return void
     */
    /**
     * Define money attributes for HasMoneyAttributes trait.
     *
     * @return array
     */
    protected function getMoneyAttributes(): array
    {
        return ['price'];
    }


    /**
     * Register media collections for product images.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }
}
