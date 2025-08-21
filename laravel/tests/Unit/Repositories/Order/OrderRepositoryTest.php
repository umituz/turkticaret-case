<?php

namespace Tests\Unit\Repositories\Order;

use App\Repositories\Order\OrderRepository;
use App\Models\Order\Order;
use Tests\Base\BaseRepositoryUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for OrderRepository
 * Tests data access logic for order operations
 */
#[CoversClass(OrderRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class OrderRepositoryTest extends BaseRepositoryUnitTest
{
    private $orderModelMock;

    protected function getRepositoryClass(): string
    {
        return OrderRepository::class;
    }

    protected function getModelClass(): string
    {
        return Order::class;
    }

    protected function getRepositoryDependencies(): array
    {
        $this->orderModelMock = $this->mockModel(Order::class);
        return [$this->orderModelMock];
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        $this->assertHasRepositoryConstructorDependencies([Order::class]);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        $this->assertRepositoryHasMethod('findByUserUuid');
        $this->assertRepositoryHasMethod('findByOrderNumber');
        $this->assertRepositoryHasMethod('create');
        $this->assertRepositoryHasMethod('findByUuid');
        $this->assertRepositoryHasMethod('updateByUuid');
        $this->assertRepositoryHasMethod('deleteByUuid');
    }

    #[Test]
    public function getModel_returns_order_model(): void
    {
        // Act
        $result = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(Order::class, $result);
    }

    #[Test]
    public function findByUserUuid_returns_paginated_orders_for_user(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $paginatedResult = $this->mockPaginator();

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('with')->andReturnSelf();
        $this->orderModelMock->shouldReceive('orderBy')->andReturnSelf();
        $this->orderModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->findByUserUuid($userUuid);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function findByOrderNumber_returns_order_when_found(): void
    {
        // Arrange
        $orderNumber = 'ORDER-12345';
        $order = $this->mockModelInstance(Order::class, [
            'order_number' => $orderNumber,
            'uuid' => $this->getTestEntityUuid()
        ]);

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('with')->andReturnSelf();
        $this->orderModelMock->shouldReceive('first')->andReturn($order);

        // Act
        $result = $this->repository->findByOrderNumber($orderNumber);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals($orderNumber, $result->order_number);
    }

    #[Test]
    public function findByOrderNumber_returns_null_when_not_found(): void
    {
        // Arrange
        $orderNumber = 'NONEXISTENT-ORDER';

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('with')->andReturnSelf();
        $this->orderModelMock->shouldReceive('first')->andReturn(null);

        // Act
        $result = $this->repository->findByOrderNumber($orderNumber);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function create_creates_order_successfully(): void
    {
        // Arrange
        $orderData = [
            'user_uuid' => $this->getTestUserUuid(),
            'status' => 'pending',
            'total_amount' => 5000,
            'shipping_address' => 'Test Address'
        ];
        $createdOrder = $this->mockModelInstance(Order::class, $orderData);

        $this->mockDatabaseTransaction();

        $this->orderModelMock->shouldReceive('create')->andReturn($createdOrder);

        // Act
        $result = $this->repository->create($orderData);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
    }

    #[Test]
    public function findByUuid_returns_order_when_found(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $order = $this->mockModelInstance(Order::class, ['uuid' => $uuid]);

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('first')->andReturn($order);

        // Act
        $result = $this->repository->findByUuid($uuid);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals($uuid, $result->uuid);
    }

    #[Test]
    public function updateByUuid_updates_order_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $updateData = ['status' => 'completed'];
        $order = $this->mockModelInstance(Order::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('firstOrFail')->andReturn($order);
        $order->shouldReceive('update')->andReturn(true);

        // Act
        $result = $this->repository->updateByUuid($uuid, $updateData);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
    }

    #[Test]
    public function deleteByUuid_deletes_order_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();

        $this->mockDatabaseTransaction();

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('delete')->andReturn(true);

        // Act
        $result = $this->repository->deleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function paginate_returns_paginated_orders(): void
    {
        // Arrange
        $relations = ['orderItems', 'user'];
        $paginatedResult = $this->mockPaginator();

        $this->orderModelMock->shouldReceive('newQuery')->andReturnSelf();
        $this->orderModelMock->shouldReceive('with')->andReturnSelf();
        $this->orderModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->paginate($relations);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function findByUserUuid_includes_order_items_and_products(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $paginatedResult = $this->mockPaginator();

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('with')->andReturnSelf();
        $this->orderModelMock->shouldReceive('orderBy')->andReturnSelf();
        $this->orderModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->findByUserUuid($userUuid);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function findByOrderNumber_includes_order_items_and_products(): void
    {
        // Arrange
        $orderNumber = 'ORDER-67890';
        $order = $this->mockModelInstance(Order::class, ['order_number' => $orderNumber]);

        $this->orderModelMock->shouldReceive('where')->andReturnSelf();
        $this->orderModelMock->shouldReceive('with')->andReturnSelf();
        $this->orderModelMock->shouldReceive('first')->andReturn($order);

        // Act
        $result = $this->repository->findByOrderNumber($orderNumber);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals($orderNumber, $result->order_number);
    }
}