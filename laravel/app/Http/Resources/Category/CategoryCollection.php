<?php

namespace App\Http\Resources\Category;

use App\Http\Resources\Base\BaseCollection;

/**
 * API Resource Collection for transforming Category data.
 * 
 * Handles the transformation of multiple Category model instances into standardized
 * JSON API response collections. Provides consistent pagination, filtering,
 * and collection metadata for category listings and hierarchical displays.
 *
 * @package App\Http\Resources\Category
 */
class CategoryCollection extends BaseCollection
{
    public $collects = CategoryResource::class;
}