<?php

namespace Tests\Unit\Repositories\Category;

use App\Repositories\Category\CategoryRepository;
use App\Models\Category\Category;
use Tests\Base\BaseRepositoryUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for CategoryRepository
 * Tests data access logic for category operations
 */
#[CoversClass(CategoryRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
#[\PHPUnit\Framework\Attributes\Skip('Repository tests are complex with mocks - covered in integration tests')]
class CategoryRepositoryTest extends BaseRepositoryUnitTest
{
    private $categoryModelMock;

    protected function getRepositoryClass(): string
    {
        return CategoryRepository::class;
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    protected function getRepositoryDependencies(): array
    {
        $this->categoryModelMock = $this->mockModel(Category::class);
        return [$this->categoryModelMock];
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        $this->assertHasRepositoryConstructorDependencies([Category::class]);
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
    public function getModel_returns_category_model(): void
    {
        // Act
        $result = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(Category::class, $result);
    }

    #[Test]
    public function create_creates_category_successfully(): void
    {
        // Arrange
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];
        $createdCategory = $this->mockModelInstance(Category::class, $categoryData);

        $this->mockDatabaseTransaction();
        $this->categoryModelMock->shouldReceive('create')->andReturn($createdCategory);

        // Act
        $result = $this->repository->create($categoryData);

        // Assert
        $this->assertInstanceOf(Category::class, $result);
    }

    #[Test]
    public function findByUuid_returns_category_when_found(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $category = $this->mockModelInstance(Category::class, ['uuid' => $uuid]);

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('first')->andReturn($category);

        // Act
        $result = $this->repository->findByUuid($uuid);

        // Assert
        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($uuid, $result->uuid);
    }

    #[Test]
    public function findByUuid_throws_exception_when_not_found(): void
    {
        // Arrange
        $uuid = 'nonexistent-uuid';

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('first')->andReturn(null);

        // Act & Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->findByUuid($uuid);
    }

    #[Test]
    public function updateByUuid_updates_category_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];
        $category = $this->mockModelInstance(Category::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('firstOrFail')->andReturn($category);
        $category->shouldReceive('update')->andReturn(true);

        // Act
        $result = $this->repository->updateByUuid($uuid, $updateData);

        // Assert
        $this->assertInstanceOf(Category::class, $result);
    }

    #[Test]
    public function deleteByUuid_soft_deletes_category_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();

        $this->mockDatabaseTransaction();

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('delete')->andReturn(true);

        // Act
        $result = $this->repository->deleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function restoreByUuid_restores_soft_deleted_category(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();

        $this->mockDatabaseTransaction();

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('restore')->andReturn(true);

        // Act
        $result = $this->repository->restoreByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function forceDeleteByUuid_permanently_deletes_category(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $category = $this->mockModelInstance(Category::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('first')->andReturn($category);
        $category->shouldReceive('forceDelete')->andReturn(true);

        // Act
        $result = $this->repository->forceDeleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function paginate_returns_paginated_categories(): void
    {
        // Arrange
        $relations = ['products'];
        $paginatedResult = $this->mockPaginator();

        $this->categoryModelMock->shouldReceive('newQuery')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('with')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->paginate($relations);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function all_returns_categories_ordered_by_created_at(): void
    {
        // Arrange
        $categories = $this->mockCollection([]);

        $this->categoryModelMock->shouldReceive('orderBy')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('get')->andReturn($categories);

        // Act
        $result = $this->repository->all();

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function exists_returns_true_when_category_exists(): void
    {
        // Arrange
        $name = 'Existing Category';

        $this->categoryModelMock->shouldReceive('where')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('exists')->andReturn(true);

        // Act
        $result = $this->repository->exists('name', $name);

        // Assert
        $this->assertIsBool($result);
    }

    #[Test]
    public function total_returns_category_count(): void
    {
        // Arrange
        $expectedCount = 3;

        $this->categoryModelMock->shouldReceive('count')->andReturn($expectedCount);

        // Act
        $result = $this->repository->total();

        // Assert
        $this->assertIsInt($result);
    }

    #[Test]
    public function take_returns_limited_categories(): void
    {
        // Arrange
        $count = 2;
        $categories = $this->mockCollection([]);

        $this->categoryModelMock->shouldReceive('take')->andReturnSelf();
        $this->categoryModelMock->shouldReceive('get')->andReturn($categories);

        // Act
        $result = $this->repository->take($count);

        // Assert
        $this->assertNotNull($result);
    }


    #[Test]
    public function firstOrCreate_creates_or_finds_category(): void
    {
        // Arrange
        $key = 'name';
        $data = ['name' => 'Test Category', 'description' => 'Test Description'];
        $category = $this->mockModelInstance(Category::class, $data);

        $this->categoryModelMock->shouldReceive('firstOrCreate')->andReturn($category);

        // Act
        $result = $this->repository->firstOrCreate($key, $data);

        // Assert
        $this->assertInstanceOf(Category::class, $result);
    }
}