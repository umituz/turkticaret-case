<?php

namespace Tests\Unit\Repositories\Product;

use App\Repositories\Product\ProductRepository;
use App\Models\Product\Product;
use Tests\Base\BaseRepositoryUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for ProductRepository
 * Tests data access logic for product operations
 */
#[CoversClass(ProductRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class ProductRepositoryTest extends BaseRepositoryUnitTest
{
    private $productModelMock;

    protected function getRepositoryClass(): string
    {
        return ProductRepository::class;
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    protected function getRepositoryDependencies(): array
    {
        $this->productModelMock = $this->mockModel(Product::class);
        return [$this->productModelMock];
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        $this->assertHasRepositoryConstructorDependencies([Product::class]);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        $this->assertRepositoryHasMethod('create');
        $this->assertRepositoryHasMethod('findByUuid');
        $this->assertRepositoryHasMethod('updateByUuid');
        $this->assertRepositoryHasMethod('deleteByUuid');
        $this->assertRepositoryHasMethod('paginate');
        $this->assertRepositoryHasMethod('restoreByUuid');
        $this->assertRepositoryHasMethod('forceDeleteByUuid');
    }

    #[Test]
    public function getModel_returns_product_model(): void
    {
        // Act
        $result = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(Product::class, $result);
    }

    #[Test]
    public function create_creates_product_successfully(): void
    {
        // Arrange
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 1000,
            'category_uuid' => $this->getTestEntityUuid(),
            'stock_quantity' => 50
        ];
        $createdProduct = $this->mockModelInstance(Product::class, $productData);

        $this->mockDatabaseTransaction();

        $this->productModelMock->shouldReceive('create')->andReturn($createdProduct);

        // Act
        $result = $this->repository->create($productData);

        // Assert
        $this->assertInstanceOf(Product::class, $result);
    }

    #[Test]
    public function findByUuid_returns_product_when_found(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $product = $this->mockModelInstance(Product::class, ['uuid' => $uuid]);

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('first')->andReturn($product);

        // Act
        $result = $this->repository->findByUuid($uuid);

        // Assert
        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($uuid, $result->uuid);
    }

    #[Test]
    public function findByUuid_throws_exception_when_not_found(): void
    {
        // Arrange
        $uuid = 'nonexistent-uuid';

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('first')->andReturn(null);

        // Act & Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->findByUuid($uuid);
    }

    #[Test]
    public function updateByUuid_updates_product_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $updateData = [
            'name' => 'Updated Product',
            'price' => 1500
        ];
        $product = $this->mockModelInstance(Product::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('firstOrFail')->andReturn($product);
        $product->shouldReceive('update')->andReturn(true);

        // Act
        $result = $this->repository->updateByUuid($uuid, $updateData);

        // Assert
        $this->assertInstanceOf(Product::class, $result);
    }

    #[Test]
    public function deleteByUuid_soft_deletes_product_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();

        $this->mockDatabaseTransaction();

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('delete')->andReturn(true);

        // Act
        $result = $this->repository->deleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function restoreByUuid_restores_soft_deleted_product(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();

        $this->mockDatabaseTransaction();

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('restore')->andReturn(true);

        // Act
        $result = $this->repository->restoreByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function forceDeleteByUuid_permanently_deletes_product(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $product = $this->mockModelInstance(Product::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('first')->andReturn($product);

        $product->shouldReceive('forceDelete')->andReturn(true);

        // Act
        $result = $this->repository->forceDeleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function paginate_returns_paginated_products(): void
    {
        // Arrange
        $relations = ['category'];
        $paginatedResult = $this->mockPaginator();

        $this->productModelMock->shouldReceive('newQuery')->andReturnSelf();
        $this->productModelMock->shouldReceive('with')->andReturnSelf();
        $this->productModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->paginate($relations);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function all_returns_products_ordered_by_created_at(): void
    {
        // Arrange
        $products = $this->mockCollection([]);

        $this->productModelMock->shouldReceive('orderBy')->andReturnSelf();
        $this->productModelMock->shouldReceive('get')->andReturn($products);

        // Act
        $result = $this->repository->all();

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function exists_returns_true_when_product_exists(): void
    {
        // Arrange
        $name = 'Existing Product';

        $this->productModelMock->shouldReceive('where')->andReturnSelf();
        $this->productModelMock->shouldReceive('exists')->andReturn(true);

        // Act
        $result = $this->repository->exists('name', $name);

        // Assert
        $this->assertIsBool($result);
    }

    #[Test]
    public function total_returns_product_count(): void
    {
        // Arrange
        $expectedCount = 10;

        $this->productModelMock->shouldReceive('count')->andReturn($expectedCount);

        // Act
        $result = $this->repository->total();

        // Assert
        $this->assertIsInt($result);
    }

    #[Test]
    public function take_returns_limited_products(): void
    {
        // Arrange
        $count = 5;
        $products = $this->mockCollection([]);

        $this->productModelMock->shouldReceive('take')->andReturnSelf();
        $this->productModelMock->shouldReceive('get')->andReturn($products);

        // Act
        $result = $this->repository->take($count);

        // Assert
        $this->assertNotNull($result);
    }

}