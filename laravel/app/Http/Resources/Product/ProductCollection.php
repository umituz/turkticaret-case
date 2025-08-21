<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Base\BaseCollection;

class ProductCollection extends BaseCollection
{
    public $collects = ProductResource::class;
}