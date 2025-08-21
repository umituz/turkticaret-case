<?php

namespace App\Services\Category;

use App\Models\Category\Category;
use App\Repositories\Category\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categoryRepository) {}

    public function paginate(): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate();
    }

    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->categoryRepository->updateByUuid($category->uuid, $data);
    }

    public function delete(Category $category): void
    {
        $this->categoryRepository->deleteByUuid($category->uuid);
    }

    public function restore(Category $category): Category
    {
        $this->categoryRepository->restoreByUuid($category->uuid);
        $category->refresh();
        return $category;
    }

    public function forceDelete(Category $category): void
    {
        $this->categoryRepository->forceDeleteByUuid($category->uuid);
    }
}
