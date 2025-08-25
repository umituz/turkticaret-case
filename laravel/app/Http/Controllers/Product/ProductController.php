<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\ProductCreateRequest;
use App\Http\Requests\Product\ProductListRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for Product management.
 * 
 * Handles CRUD operations and additional product-related functionality including
 * soft deletes. All responses are formatted as standardized JSON API responses.
 *
 * @package App\Http\Controllers\Product
 */
class ProductController extends BaseController
{
    /**
     * Create a new ProductController instance.
     *
     * @param ProductService $productService The product service for business logic operations
     */
    public function __construct(protected ProductService $productService) {}

    /**
     * Display a paginated listing of products with optional filtering.
     *
     * @param ProductListRequest $request The validated request containing filter parameters
     * @return JsonResponse JSON response containing paginated product collection
     */
    public function index(ProductListRequest $request): JsonResponse
    {
        $products = $this->productService->paginate($request->filters());

        return $this->ok(new ProductCollection($products));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param ProductCreateRequest $request The validated request containing product data
     * @return JsonResponse JSON response containing the created product resource with 201 status
     */
    public function store(ProductCreateRequest $request): JsonResponse
    {
        return $this->created(new ProductResource($this->productService->create($request->validated())));
    }

    /**
     * Display the specified product.
     *
     * @param Product $product The product model instance resolved by route model binding
     * @return JsonResponse JSON response containing the product resource
     */
    public function show(Product $product): JsonResponse
    {
        return $this->ok(new ProductResource($product));
    }

    /**
     * Update the specified product in storage.
     *
     * @param ProductUpdateRequest $request The validated request containing updated product data
     * @param Product $product The product model instance resolved by route model binding
     * @return JsonResponse JSON response containing the updated product resource
     */
    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        return $this->ok(new ProductResource($this->productService->update($product, $request->validated())));
    }

    /**
     * Soft delete the specified product from storage.
     *
     * @param Product $product The product model instance resolved by route model binding
     * @return JsonResponse JSON response with 204 No Content status
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return $this->noContent();
    }

    /**
     * Restore a soft deleted product.
     *
     * @param Product $product The product model instance resolved by route model binding
     * @return JsonResponse JSON response containing the restored product resource
     */
    public function restore(Product $product): JsonResponse
    {
        return $this->ok(new ProductResource($this->productService->restore($product)));
    }

    /**
     * Permanently delete the specified product from storage.
     *
     * @param Product $product The product model instance resolved by route model binding
     * @return JsonResponse JSON response with 204 No Content status
     */
    public function forceDelete(Product $product): JsonResponse
    {
        $this->productService->forceDelete($product);

        return $this->noContent();
    }


}
