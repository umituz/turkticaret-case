<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\ProductCreateRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseController
{
    public function __construct(protected ProductService $productService) {}

    public function index(): JsonResponse
    {
        return $this->ok(new ProductCollection($this->productService->paginate()));
    }

    public function store(ProductCreateRequest $request): JsonResponse
    {
        return $this->ok(new ProductResource($this->productService->create($request->validated())));
    }

    public function show(Product $product): JsonResponse
    {
        return $this->ok(new ProductResource($product));
    }

    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        return $this->ok(new ProductResource($this->productService->update($product, $request->validated())));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return $this->noContent();
    }

    public function restore(Product $product): JsonResponse
    {
        return $this->ok(new ProductResource($this->productService->restore($product)));
    }

    public function forceDelete(Product $product): JsonResponse
    {
        $this->productService->forceDelete($product);

        return $this->noContent();
    }
}