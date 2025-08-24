<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use App\Repositories\Base\BaseRepository;

/**
 * Product repository for handling product-related database operations.
 * 
 * This repository provides methods for managing product data including
 * standard CRUD operations and product-specific queries.
 *
 * @package App\Repositories\Product
 */
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * Create a new Product repository instance.
     *
     * @param Product $model The Product model instance
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}