<?php

namespace App\Models\Category;

use App\Models\Base\BaseUuidModel;
use App\Models\Product\Product;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Relations\HasMany;

 /**
 * Category Model representing product categories in the e-commerce system.
 *
 * Handles product categorization, hierarchical organization, and slug generation
 * for SEO-friendly URLs. Provides relationship management between categories
 * and products for effective catalog organization.
 *
 * @property string $uuid Category unique identifier
 * @property string $name Category name
 * @property string|null $description Category description
 * @property string $slug URL-friendly category slug
 * @property bool $is_active Whether the category is active and visible
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 *
 * @package App\Models\Category
 */
class Category extends BaseUuidModel
{
    use HasSlug;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all products that belong to this category.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_uuid', 'uuid');
    }
}
