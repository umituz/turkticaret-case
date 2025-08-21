<?php

namespace App\Http\Resources\Category;

use App\Http\Resources\Base\BaseCollection;

class CategoryCollection extends BaseCollection
{
    public $collects = CategoryResource::class;
}