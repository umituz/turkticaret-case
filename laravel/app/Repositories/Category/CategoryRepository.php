<?php

namespace App\Repositories\Category;

use App\Models\Category\Category;
use App\Repositories\Base\BaseRepository;

/**
 * Repository class for managing category data operations.
 * 
 * This repository extends the BaseRepository to provide category-specific
 * database operations and queries. It inherits all CRUD operations from
 * the base repository.
 * 
 * @package App\Repositories\Category
 */
class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /**
     * Create a new CategoryRepository instance.
     * 
     * @param Category $model The Category model instance to be injected
     */
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
}