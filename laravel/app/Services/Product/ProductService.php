<?php

namespace App\Services\Product;

use App\Filters\Product\ProductFilterHandler;
use App\Models\Product\Product;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(protected ProductRepositoryInterface $productRepository) {}

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

    public function create(array $data): Product
    {
        $product = $this->productRepository->create($data);

        if (isset($data['media']) && $data['media']) {
            $product->addMediaFromRequest('media')
                ->toMediaCollection('images');
        }

        return $product;
    }

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

    public function delete(Product $product): void
    {
        $this->productRepository->deleteByUuid($product->uuid);
    }

    public function restore(Product $product): Product
    {
        $this->productRepository->restoreByUuid($product->uuid);
        $product->refresh();

        return $product;
    }

    public function forceDelete(Product $product): void
    {
        $this->productRepository->forceDeleteByUuid($product->uuid);
    }

    public function validateStock(Product $product, int $requestedQuantity): void
    {
        if (!$product->isInStock()) {
            throw new OutOfStockException($product->name);
        }

        if (!$product->hasStock($requestedQuantity)) {
            throw new InsufficientStockException($product->name, $requestedQuantity, $product->stock_quantity);
        }
    }
}
