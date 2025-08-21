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

class CategoryController extends BaseController
{
    public function __construct(protected CategoryService $categoryService) {}

    public function index(): JsonResponse
    {
        return $this->ok(new CategoryCollection($this->categoryService->paginate()));
    }

    public function store(CategoryCreateRequest $request): JsonResponse
    {
        return $this->created(new CategoryResource($this->categoryService->create($request->validated())));
    }

    public function show(Category $category): JsonResponse
    {
        return $this->ok(new CategoryResource($category));
    }

    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        return $this->ok(new CategoryResource($this->categoryService->update($category, $request->validated())));
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return $this->noContent();
    }

    public function restore(Category $category): JsonResponse
    {
        return $this->ok(new CategoryResource($this->categoryService->restore($category)));
    }

    public function forceDelete(Category $category): JsonResponse
    {
        $this->categoryService->forceDelete($category);

        return $this->noContent();
    }
}
