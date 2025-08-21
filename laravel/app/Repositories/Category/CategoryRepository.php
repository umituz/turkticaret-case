<?php

namespace App\Repositories\Category;

use App\Models\Category\Category;
use App\Repositories\Base\BaseRepository;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
}