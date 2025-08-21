<?php

namespace App\Models\Category;

use App\Models\Base\BaseUuidModel;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends BaseUuidModel
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_uuid', 'uuid');
    }
}
