<?php

namespace Tests\Unit\Services\Category;

use App\Services\Category\CategoryService;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Models\Category\Category;
use Tests\Base\BaseServiceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for CategoryService
 * Tests CRUD operations and soft delete functionality for categories
 */
#[CoversClass(CategoryService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class CategoryServiceTest extends BaseServiceUnitTest
{
    private CategoryRepositoryInterface $categoryRepositoryMock;

    protected function getServiceClass(): string
    {
        return CategoryService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->categoryRepositoryMock = $this->mockRepository(CategoryRepositoryInterface::class);

        return [
            $this->categoryRepositoryMock
        ];
    }

    #[Test]
    public function service_has_required_constructor_dependencies(): void
    {
        $this->assertHasConstructorDependencies([
            CategoryRepositoryInterface::class
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
    public function paginate_returns_paginated_categories(): void
    {
        // Arrange
        $expectedResult = $this->mockPaginator([]);

        $this->categoryRepositoryMock
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($expectedResult);

        // Act
        $result = $this->service->paginate();

        // Assert
        $this->assertServiceReturns($result, \Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        $this->assertServiceUsesRepository($this->categoryRepositoryMock, 'paginate');
    }

    #[Test]
    public function create_creates_new_category(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];
        $category = $this->createMockCategory($data);

        $this->categoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($category);

        // Act
        $result = $this->service->create($data);

        // Assert
        $this->assertServiceReturns($result, Category::class);
        $this->assertServiceUsesRepository($this->categoryRepositoryMock, 'create', [$data]);
    }

    #[Test]
    public function update_updates_existing_category(): void
    {
        // Arrange
        $category = $this->createMockCategory();
        $data = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];
        $updatedCategory = $this->createMockCategory($data);

        $this->categoryRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($category->uuid, $data)
            ->andReturn($updatedCategory);

        // Act
        $result = $this->service->update($category, $data);

        // Assert
        $this->assertServiceReturns($result, Category::class);
        $this->assertServiceUsesRepository($this->categoryRepositoryMock, 'updateByUuid', [$category->uuid, $data]);
    }

    #[Test]
    public function delete_soft_deletes_category(): void
    {
        // Arrange
        $category = $this->createMockCategory();

        $this->categoryRepositoryMock
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($category->uuid)
            ->andReturn(true);

        // Act
        $this->service->delete($category);

        // Assert
        $this->assertServiceUsesRepository($this->categoryRepositoryMock, 'deleteByUuid', [$category->uuid]);
    }

    #[Test]
    public function restore_restores_soft_deleted_category(): void
    {
        // Arrange
        $category = $this->createMockCategory();

        $this->categoryRepositoryMock
            ->shouldReceive('restoreByUuid')
            ->once()
            ->with($category->uuid)
            ->andReturn(true);

        $category->shouldReceive('refresh')
            ->once()
            ->andReturnSelf();

        // Act
        $result = $this->service->restore($category);

        // Assert
        $this->assertServiceReturns($result, Category::class);
        $this->assertServiceUsesRepository($this->categoryRepositoryMock, 'restoreByUuid', [$category->uuid]);
    }

    #[Test]
    public function forceDelete_permanently_deletes_category(): void
    {
        // Arrange
        $category = $this->createMockCategory();

        $this->categoryRepositoryMock
            ->shouldReceive('forceDeleteByUuid')
            ->once()
            ->with($category->uuid)
            ->andReturn(true);

        // Act
        $this->service->forceDelete($category);

        // Assert
        $this->assertServiceUsesRepository($this->categoryRepositoryMock, 'forceDeleteByUuid', [$category->uuid]);
    }

    #[Test]
    public function create_with_minimal_data(): void
    {
        // Arrange
        $data = ['name' => 'Minimal Category'];
        $category = $this->createMockCategory($data);

        $this->categoryRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($category);

        // Act
        $result = $this->service->create($data);

        // Assert
        $this->assertServiceReturns($result, Category::class);
    }

    #[Test]
    public function update_with_partial_data(): void
    {
        // Arrange
        $category = $this->createMockCategory();
        $data = ['name' => 'Partially Updated Name'];
        $updatedCategory = $this->createMockCategory($data);

        $this->categoryRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($category->uuid, $data)
            ->andReturn($updatedCategory);

        // Act
        $result = $this->service->update($category, $data);

        // Assert
        $this->assertServiceReturns($result, Category::class);
    }

    /**
     * Create mock Category
     */
    private function createMockCategory(array $attributes = []): \Mockery\MockInterface
    {
        $defaultAttributes = [
            'uuid' => $this->getTestEntityUuid(),
            'name' => 'Test Category',
            'description' => 'Test Description',
        ];

        return $this->mockTypedModel(Category::class, array_merge($defaultAttributes, $attributes));
    }
}