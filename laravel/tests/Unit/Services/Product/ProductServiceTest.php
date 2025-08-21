<?php

namespace Tests\Unit\Services\Product;

use App\Services\Product\ProductService;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Models\Product\Product;
use Tests\Base\BaseServiceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for ProductService
 * Tests CRUD operations and soft delete functionality for products
 */
#[CoversClass(ProductService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class ProductServiceTest extends BaseServiceUnitTest
{
    private ProductRepositoryInterface $productRepositoryMock;

    protected function getServiceClass(): string
    {
        return ProductService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->productRepositoryMock = $this->mockRepository(ProductRepositoryInterface::class);

        return [
            $this->productRepositoryMock
        ];
    }

    #[Test]
    public function service_has_required_constructor_dependencies(): void
    {
        $this->assertHasConstructorDependencies([
            ProductRepositoryInterface::class
        ]);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        $this->assertServiceHasMethod('paginate');
        $this->assertServiceHasMethod('create');
        $this->assertServiceHasMethod('update');
        $this->assertServiceHasMethod('delete');
        $this->assertServiceHasMethod('restore');
        $this->assertServiceHasMethod('forceDelete');
    }

    #[Test]
    public function paginate_returns_paginated_products(): void
    {
        // Arrange
        $expectedResult = $this->mockPaginator([]);

        $this->productRepositoryMock
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($expectedResult);

        // Act
        $result = $this->service->paginate();

        // Assert
        $this->assertServiceReturns($result, \Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        $this->assertServiceUsesRepository($this->productRepositoryMock, 'paginate');
    }

    #[Test]
    public function create_creates_new_product(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 1000,
            'category_uuid' => $this->getTestEntityUuid()
        ];
        $product = $this->createMockProduct($data);

        $this->productRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($product);

        // Act
        $result = $this->service->create($data);

        // Assert
        $this->assertServiceReturns($result, Product::class);
        $this->assertServiceUsesRepository($this->productRepositoryMock, 'create', [$data]);
    }

    #[Test]
    public function update_updates_existing_product(): void
    {
        // Arrange
        $product = $this->createMockProduct();
        $data = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 1500
        ];
        $updatedProduct = $this->createMockProduct($data);

        $this->productRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($product->uuid, $data)
            ->andReturn($updatedProduct);

        // Act
        $result = $this->service->update($product, $data);

        // Assert
        $this->assertServiceReturns($result, Product::class);
        $this->assertServiceUsesRepository($this->productRepositoryMock, 'updateByUuid', [$product->uuid, $data]);
    }

    #[Test]
    public function delete_soft_deletes_product(): void
    {
        // Arrange
        $product = $this->createMockProduct();

        $this->productRepositoryMock
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($product->uuid)
            ->andReturn(true);

        // Act
        $this->service->delete($product);

        // Assert
        $this->assertServiceUsesRepository($this->productRepositoryMock, 'deleteByUuid', [$product->uuid]);
    }

    #[Test]
    public function restore_restores_soft_deleted_product(): void
    {
        // Arrange
        $product = $this->createMockProduct();

        $this->productRepositoryMock
            ->shouldReceive('restoreByUuid')
            ->once()
            ->with($product->uuid)
            ->andReturn(true);

        $product->shouldReceive('refresh')
            ->once()
            ->andReturnSelf();

        // Act
        $result = $this->service->restore($product);

        // Assert
        $this->assertServiceReturns($result, Product::class);
        $this->assertServiceUsesRepository($this->productRepositoryMock, 'restoreByUuid', [$product->uuid]);
    }

    #[Test]
    public function forceDelete_permanently_deletes_product(): void
    {
        // Arrange
        $product = $this->createMockProduct();

        $this->productRepositoryMock
            ->shouldReceive('forceDeleteByUuid')
            ->once()
            ->with($product->uuid)
            ->andReturn(true);

        // Act
        $this->service->forceDelete($product);

        // Assert
        $this->assertServiceUsesRepository($this->productRepositoryMock, 'forceDeleteByUuid', [$product->uuid]);
    }

    #[Test]
    public function create_with_all_required_fields(): void
    {
        // Arrange
        $data = [
            'name' => 'Complete Product',
            'description' => 'Complete Description',
            'price' => 2500,
            'category_uuid' => $this->getTestEntityUuid(),
            'stock_quantity' => 100
        ];
        $product = $this->createMockProduct($data);

        $this->productRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($product);

        // Act
        $result = $this->service->create($data);

        // Assert
        $this->assertServiceReturns($result, Product::class);
    }

    #[Test]
    public function update_with_price_change(): void
    {
        // Arrange
        $product = $this->createMockProduct(['price' => 1000]);
        $data = ['price' => 1200];
        $updatedProduct = $this->createMockProduct(['price' => 1200]);

        $this->productRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($product->uuid, $data)
            ->andReturn($updatedProduct);

        // Act
        $result = $this->service->update($product, $data);

        // Assert
        $this->assertServiceReturns($result, Product::class);
    }

    #[Test]
    public function update_with_stock_quantity_change(): void
    {
        // Arrange
        $product = $this->createMockProduct(['stock_quantity' => 50]);
        $data = ['stock_quantity' => 75];
        $updatedProduct = $this->createMockProduct(['stock_quantity' => 75]);

        $this->productRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($product->uuid, $data)
            ->andReturn($updatedProduct);

        // Act
        $result = $this->service->update($product, $data);

        // Assert
        $this->assertServiceReturns($result, Product::class);
    }

    /**
     * Create mock Product
     */
    private function createMockProduct(array $attributes = []): \Mockery\MockInterface
    {
        $defaultAttributes = [
            'uuid' => $this->getTestEntityUuid(),
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 1000,
            'category_uuid' => $this->getTestEntityUuid(),
            'stock_quantity' => 50,
        ];

        return $this->mockTypedModel(Product::class, array_merge($defaultAttributes, $attributes));
    }
}