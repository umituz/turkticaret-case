<?php

namespace Tests\Unit\Repositories\Order;

use App\Repositories\Order\OrderStatusRepository;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Enums\Order\OrderStatusEnum;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Support\Facades\Auth;
use Mockery;

/**
 * Unit tests for OrderStatusRepository
 * Tests order status updates with history tracking
 */
#[CoversClass(OrderStatusRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class OrderStatusRepositoryTest extends UnitTestCase
{
    private OrderStatusRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrderStatusRepository();
    }

    #[Test]
    public function update_status_changes_order_status_and_creates_history(): void
    {
        // Arrange
        Auth::shouldReceive('id')->once()->andReturn('admin-user-id');
        
        $order = Mockery::mock(Order::class);
        $order->uuid = 'test-order-uuid';
        $order->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(OrderStatusEnum::PENDING);
        $order->shouldReceive('setAttribute')
            ->with('status', OrderStatusEnum::CONFIRMED);
        $order->shouldReceive('save')
            ->once()
            ->andReturn(true);

        OrderStatusHistory::shouldReceive('create')
            ->once()
            ->with([
                'order_uuid' => 'test-order-uuid',
                'old_status' => OrderStatusEnum::PENDING->value,
                'new_status' => OrderStatusEnum::CONFIRMED->value,
                'changed_by_uuid' => 'admin-user-id',
                'notes' => 'Status updated via admin panel',
            ]);

        // Act
        $result = $this->repository->updateStatus($order, OrderStatusEnum::CONFIRMED);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function update_status_returns_false_when_order_save_fails(): void
    {
        // Arrange
        Auth::shouldReceive('id')->once()->andReturn('admin-user-id');
        
        $order = Mockery::mock(Order::class);
        $order->uuid = 'test-order-uuid';
        $order->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(OrderStatusEnum::PENDING);
        $order->shouldReceive('setAttribute')
            ->with('status', OrderStatusEnum::PROCESSING);
        $order->shouldReceive('save')
            ->once()
            ->andReturn(false);

        // Should not create history when save fails
        OrderStatusHistory::shouldNotReceive('create');

        // Act
        $result = $this->repository->updateStatus($order, OrderStatusEnum::PROCESSING);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function update_status_records_auth_user_as_changed_by(): void
    {
        // Arrange
        Auth::shouldReceive('id')->once()->andReturn('specific-admin-id');
        
        $order = Mockery::mock(Order::class);
        $order->uuid = 'test-order-uuid';
        $order->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(OrderStatusEnum::CONFIRMED);
        $order->shouldReceive('setAttribute')
            ->with('status', OrderStatusEnum::SHIPPED);
        $order->shouldReceive('save')
            ->once()
            ->andReturn(true);

        OrderStatusHistory::shouldReceive('create')
            ->once()
            ->with([
                'order_uuid' => 'test-order-uuid',
                'old_status' => OrderStatusEnum::CONFIRMED->value,
                'new_status' => OrderStatusEnum::SHIPPED->value,
                'changed_by_uuid' => 'specific-admin-id',
                'notes' => 'Status updated via admin panel',
            ]);

        // Act
        $result = $this->repository->updateStatus($order, OrderStatusEnum::SHIPPED);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function update_status_handles_null_auth_user(): void
    {
        // Arrange
        Auth::shouldReceive('id')->once()->andReturn(null);
        
        $order = Mockery::mock(Order::class);
        $order->uuid = 'test-order-uuid';
        $order->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(OrderStatusEnum::PROCESSING);
        $order->shouldReceive('setAttribute')
            ->with('status', OrderStatusEnum::DELIVERED);
        $order->shouldReceive('save')
            ->once()
            ->andReturn(true);

        OrderStatusHistory::shouldReceive('create')
            ->once()
            ->with([
                'order_uuid' => 'test-order-uuid',
                'old_status' => OrderStatusEnum::PROCESSING->value,
                'new_status' => OrderStatusEnum::DELIVERED->value,
                'changed_by_uuid' => null,
                'notes' => 'Status updated via admin panel',
            ]);

        // Act
        $result = $this->repository->updateStatus($order, OrderStatusEnum::DELIVERED);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function update_status_creates_history_with_correct_order_uuid(): void
    {
        // Arrange
        Auth::shouldReceive('id')->once()->andReturn('test-user');
        
        $uniqueOrderUuid = 'unique-order-uuid-123';
        $order = Mockery::mock(Order::class);
        $order->uuid = $uniqueOrderUuid;
        $order->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(OrderStatusEnum::PENDING);
        $order->shouldReceive('setAttribute')
            ->with('status', OrderStatusEnum::CANCELLED);
        $order->shouldReceive('save')
            ->once()
            ->andReturn(true);

        OrderStatusHistory::shouldReceive('create')
            ->once()
            ->with([
                'order_uuid' => $uniqueOrderUuid,
                'old_status' => OrderStatusEnum::PENDING->value,
                'new_status' => OrderStatusEnum::CANCELLED->value,
                'changed_by_uuid' => 'test-user',
                'notes' => 'Status updated via admin panel',
            ]);

        // Act
        $result = $this->repository->updateStatus($order, OrderStatusEnum::CANCELLED);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function update_status_works_with_different_status_transitions(): void
    {
        // Arrange
        Auth::shouldReceive('id')->times(3)->andReturn('admin-id');
        
        $statusTransitions = [
            [OrderStatusEnum::PENDING, OrderStatusEnum::CONFIRMED],
            [OrderStatusEnum::CONFIRMED, OrderStatusEnum::PROCESSING],
            [OrderStatusEnum::PROCESSING, OrderStatusEnum::SHIPPED],
        ];

        foreach ($statusTransitions as $index => [$oldStatus, $newStatus]) {
            $order = Mockery::mock(Order::class);
            $order->uuid = "order-uuid-{$index}";
            $order->shouldReceive('getAttribute')
                ->with('status')
                ->andReturn($oldStatus);
            $order->shouldReceive('setAttribute')
                ->with('status', $newStatus);
            $order->shouldReceive('save')
                ->once()
                ->andReturn(true);

            OrderStatusHistory::shouldReceive('create')
                ->once()
                ->with([
                    'order_uuid' => "order-uuid-{$index}",
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by_uuid' => 'admin-id',
                    'notes' => 'Status updated via admin panel',
                ]);

            // Act
            $result = $this->repository->updateStatus($order, $newStatus);

            // Assert
            $this->assertTrue($result);
        }
    }

    #[Test]
    public function repository_implements_interface(): void
    {
        // Assert
        $this->assertInstanceOf(
            \App\Repositories\Order\OrderStatusRepositoryInterface::class,
            $this->repository
        );
    }
}