<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use App\Repositories\Base\BaseRepository;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}