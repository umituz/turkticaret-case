<?php

namespace App\Services\Product;

use App\Models\Product\Product;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(protected ProductRepositoryInterface $productRepository) {}

    public function paginate(): LengthAwarePaginator
    {
        return $this->productRepository->paginate();
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->productRepository->updateByUuid($product->uuid, $data);
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
}