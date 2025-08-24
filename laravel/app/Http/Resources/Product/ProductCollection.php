<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Base\BaseCollection;

/**
 * API Collection Resource for transforming Product data.
 * 
 * Handles the transformation of Product model collections into standardized
 * JSON API responses. Automatically collects ProductResource instances
 * and applies pagination, filtering and formatting for API consumption.
 *
 * @package App\Http\Resources\Product
 */
class ProductCollection extends BaseCollection
{
    public $collects = ProductResource::class;
}