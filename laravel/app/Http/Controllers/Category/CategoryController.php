<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Category\CategoryCreateRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category\Category;
use App\Services\Category\CategoryService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for Category management.
 *
 * Handles CRUD operations for product categories including hierarchical
 * category relationships, soft deletes, and category-specific functionality.
 * All responses are formatted as standardized JSON API responses.
 *
 * @package App\Http\Controllers\Category
 */
class CategoryController extends BaseController
{
    /**
     * Create a new CategoryController instance.
     *
     * @param CategoryService $categoryService The category service for business logic operations
     */
    public function __construct(protected CategoryService $categoryService) {}

    /**
     * Display a paginated listing of categories.
     *
     * @return JsonResponse JSON response containing paginated category collection
     */
    public function index(): JsonResponse
    {
        return $this->ok(new CategoryCollection($this->categoryService->paginate()));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param CategoryCreateRequest $request The validated request containing category data
     * @return JsonResponse JSON response containing the created category resource with 201 status
     */
    public function store(CategoryCreateRequest $request): JsonResponse
    {
        return $this->created(new CategoryResource($this->categoryService->create($request->validated())));
    }

    /**
     * Display the specified category.
     *
     * @param Category $category The category model instance resolved by route model binding
     * @return JsonResponse JSON response containing the category resource
     */
    public function show(Category $category): JsonResponse
    {
        return $this->ok(new CategoryResource($category));
    }

    /**
     * Update the specified category in storage.
     *
     * @param CategoryUpdateRequest $request The validated request containing updated category data
     * @param Category $category The category model instance resolved by route model binding
     * @return JsonResponse JSON response containing the updated category resource
     */
    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        return $this->ok(new CategoryResource($this->categoryService->update($category, $request->validated())));
    }

    /**
     * Soft delete the specified category from storage.
     *
     * @param Category $category The category model instance resolved by route model binding
     * @return JsonResponse JSON response with 204 No Content status
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return $this->noContent();
    }

    /**
     * Restore a soft deleted category.
     *
     * @param Category $category The category UUID from route parameter
     * @return JsonResponse JSON response containing the restored category resource
     */
    public function restore(Category $category): JsonResponse
    {
        return $this->ok(new CategoryResource($this->categoryService->restore($category)));
    }

    /**
     * Permanently delete the specified category from storage.
     *
     * @param Category $category The category UUID from route parameter
     * @return JsonResponse JSON response with 204 No Content status
     */
    public function forceDelete(Category $category): JsonResponse
    {
        $this->categoryService->forceDelete($category);

        return $this->noContent();
    }
}
