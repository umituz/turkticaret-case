<?php

namespace App\Services\Product;

use App\Filters\Product\ProductFilterHandler;
use App\Models\Product\Product;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Product Service for business logic operations.
 * 
 * Handles complex product operations including CRUD operations with media management,
 * stock validation, inventory statistics, related product recommendations,
 * and soft delete functionality. Implements business rules and data validation.
 *
 * @package App\Services\Product
 */
class ProductService
{
    /**
     * Create a new ProductService instance.
     *
     * @param ProductRepositoryInterface $productRepository The product repository for data operations
     */
    public function __construct(protected ProductRepositoryInterface $productRepository) {}

    /**
     * Get paginated products with optional filtering.
     *
     * @param array $filters Optional array of filter parameters for product search
     * @return LengthAwarePaginator Paginated collection of filtered products
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->productRepository->paginate();
        }

        return ProductFilterHandler::apply(
            $this->productRepository->getQuery(),
            $filters
        );
    }

    /**
     * Create a new product with optional media attachments.
     *
     * @param array $data Product data including optional media files
     * @return Product The newly created product instance
     */
    public function create(array $data): Product
    {
        $product = $this->productRepository->create($data);

        if (isset($data['media']) && $data['media']) {
            $product->addMediaFromRequest('media')
                ->toMediaCollection('images');
        }

        return $product;
    }

    /**
     * Update an existing product with optional media management.
     *
     * @param Product $product The product instance to update
     * @param array $data Updated product data including optional media files
     * @return Product The updated product instance
     */
    public function update(Product $product, array $data): Product
    {
        $updatedProduct = $this->productRepository->updateByUuid($product->uuid, $data);

        if (isset($data['media']) && $data['media']) {
            $updatedProduct->clearMediaCollection('images');
            $updatedProduct->addMediaFromRequest('media')
                ->toMediaCollection('images');
        }

        return $updatedProduct;
    }

    /**
     * Soft delete a product.
     *
     * @param Product $product The product instance to soft delete
     * @return void
     */
    public function delete(Product $product): void
    {
        $this->productRepository->deleteByUuid($product->uuid);
    }

    /**
     * Restore a soft deleted product.
     *
     * @param Product $product The product instance to restore
     * @return Product The restored product instance
     */
    public function restore(Product $product): Product
    {
        $this->productRepository->restoreByUuid($product->uuid);
        $product->refresh();

        return $product;
    }

    /**
     * Permanently delete a product from storage.
     *
     * @param Product $product The product instance to force delete
     * @return void
     */
    public function forceDelete(Product $product): void
    {
        $this->productRepository->forceDeleteByUuid($product->uuid);
    }

    /**
     * Validate product stock availability for a given quantity.
     *
     * @param Product $product The product to validate stock for
     * @param int $requestedQuantity The requested quantity to validate
     * @return void
     * @throws OutOfStockException When the product is completely out of stock
     * @throws InsufficientStockException When requested quantity exceeds available stock
     */
    public function validateStock(Product $product, int $requestedQuantity): void
    {
        if (!$product->isInStock()) {
            throw new OutOfStockException($product->name);
        }

        if (!$product->hasStock($requestedQuantity)) {
            throw new InsufficientStockException($product->name, $requestedQuantity, $product->stock_quantity);
        }
    }

    /**
     * Get comprehensive product statistics and inventory metrics.
     *
     * @return array Associative array containing product statistics including counts, values, and stock information
     */
    public function getStatistics(): array
    {
        $totalProducts = $this->productRepository->count();
        $activeProducts = $this->productRepository->countBy('is_active', true);
        $inactiveProducts = $totalProducts - $activeProducts;
        $featuredProducts = $this->productRepository->countBy('is_featured', true);
        $outOfStockProducts = $this->productRepository->countBy('stock_quantity', 0);
        
        // Low stock products (where stock_quantity > 0 AND stock_quantity <= low_stock_threshold)
        $lowStockProducts = $this->productRepository->getQuery()
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->whereNotNull('low_stock_threshold')
            ->count();
        
        $totalValue = $this->productRepository->getQuery()
            ->selectRaw('SUM(price * stock_quantity) as total')
            ->value('total') ?? 0;

        $averagePrice = $this->productRepository->getQuery()
            ->avg('price') ?? 0;

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'inactive_products' => $inactiveProducts,
            'featured_products' => $featuredProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'low_stock_products' => $lowStockProducts,
            'total_value' => $totalValue / 100, // Convert from cents to currency
            'average_price' => $averagePrice / 100, // Convert from cents to currency
        ];
    }

    /**
     * Get related products based on category and other criteria.
     *
     * @param Product $product The product to find related products for
     * @param int $limit Maximum number of related products to return (default: 8)
     * @return array Array of related product data
     */
    public function getRelatedProducts(Product $product, int $limit = 8): array
    {
        return $this->productRepository->getQuery()
            ->where('category_uuid', $product->category_uuid)
            ->where('uuid', '!=', $product->uuid)
            ->where('is_active', true)
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
