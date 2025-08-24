<?php

namespace App\Services\Category;

use App\Models\Category\Category;
use App\Repositories\Category\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Category Service for business logic operations.
 * 
 * Handles CRUD operations for product categories including hierarchical
 * category management, soft deletes, and category-specific business rules.
 * Implements business logic for category organization and relationships.
 *
 * @package App\Services\Category
 */
class CategoryService
{
    /**
     * Create a new CategoryService instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository The category repository for data operations
     */
    public function __construct(protected CategoryRepositoryInterface $categoryRepository) {}

    /**
     * Get paginated categories.
     *
     * @return LengthAwarePaginator Paginated collection of categories
     */
    public function paginate(): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate();
    }

    /**
     * Create a new category.
     *
     * @param array $data Category data for creation
     * @return Category The newly created category instance
     */
    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Update an existing category.
     *
     * @param Category $category The category instance to update
     * @param array $data Updated category data
     * @return Category The updated category instance
     */
    public function update(Category $category, array $data): Category
    {
        return $this->categoryRepository->updateByUuid($category->uuid, $data);
    }

    /**
     * Soft delete a category.
     *
     * @param Category $category The category instance to soft delete
     * @return void
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->deleteByUuid($category->uuid);
    }

    /**
     * Restore a soft deleted category.
     *
     * @param Category $category The category instance to restore
     * @return Category The restored category instance
     */
    public function restore(Category $category): Category
    {
        $this->categoryRepository->restoreByUuid($category->uuid);
        $category->refresh();
        return $category;
    }

    /**
     * Permanently delete a category from storage.
     *
     * @param Category $category The category instance to force delete
     * @return void
     */
    public function forceDelete(Category $category): void
    {
        $this->categoryRepository->forceDeleteByUuid($category->uuid);
    }
}
