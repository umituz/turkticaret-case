<?php

namespace Tests\Unit\Services\Order;

use App\Services\Order\OrderStatusService;
use App\Repositories\Order\OrderStatusRepositoryInterface;
use App\Models\Order\Order;
use App\Enums\Order\OrderStatusEnum;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;
use Mockery\MockInterface;

/**
 * Unit tests for OrderStatusService
 * Tests order status transitions and validations
 */
#[CoversClass(OrderStatusService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class OrderStatusServiceTest extends UnitTestCase
{
    private OrderStatusService $service;
    private OrderStatusRepositoryInterface|MockInterface $orderStatusRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->orderStatusRepository = Mockery::mock(OrderStatusRepositoryInterface::class);
        $this->service = new OrderStatusService($this->orderStatusRepository);
    }

    #[Test]
    public function update_status_updates_order_status_when_transition_is_valid(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('status')->andReturn(OrderStatusEnum::PENDING);
        
        $newStatus = OrderStatusEnum::CONFIRMED;
        $freshOrder = Mockery::mock(Order::class);

        $this->orderStatusRepository
            ->shouldReceive('updateStatus')
            ->once()
            ->with($order, $newStatus);

        $order
            ->shouldReceive('fresh')
            ->once()
            ->andReturn($freshOrder);

        // Act
        $result = $this->service->updateStatus($order, $newStatus);

        // Assert
        $this->assertSame($freshOrder, $result);
    }

    #[Test]
    public function update_status_throws_exception_when_transition_is_invalid(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('status')->andReturn(OrderStatusEnum::DELIVERED); // Final status
        
        $newStatus = OrderStatusEnum::PENDING; // Can't go backwards

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot transition from delivered to pending');
        
        $this->service->updateStatus($order, $newStatus);
    }

    #[Test]
    public function can_transition_to_returns_true_for_valid_transitions(): void
    {
        // Arrange
        $currentStatus = OrderStatusEnum::PENDING;
        $newStatus = OrderStatusEnum::CONFIRMED;

        // Act
        $result = $this->service->canTransitionTo($currentStatus, $newStatus);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function can_transition_to_returns_false_for_invalid_transitions(): void
    {
        // Arrange
        $currentStatus = OrderStatusEnum::DELIVERED; // Final status
        $newStatus = OrderStatusEnum::PENDING; // Can't go backwards

        // Act
        $result = $this->service->canTransitionTo($currentStatus, $newStatus);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function can_transition_to_allows_progression_from_pending_to_confirmed(): void
    {
        // Arrange
        $currentStatus = OrderStatusEnum::PENDING;
        $newStatus = OrderStatusEnum::CONFIRMED;

        // Act
        $result = $this->service->canTransitionTo($currentStatus, $newStatus);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function can_transition_to_allows_progression_from_confirmed_to_processing(): void
    {
        // Arrange
        $currentStatus = OrderStatusEnum::CONFIRMED;
        $newStatus = OrderStatusEnum::PROCESSING;

        // Act
        $result = $this->service->canTransitionTo($currentStatus, $newStatus);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function can_transition_to_allows_cancellation_from_any_non_final_status(): void
    {
        // Arrange & Act & Assert
        $this->assertTrue($this->service->canTransitionTo(OrderStatusEnum::PENDING, OrderStatusEnum::CANCELLED));
        $this->assertTrue($this->service->canTransitionTo(OrderStatusEnum::CONFIRMED, OrderStatusEnum::CANCELLED));
        $this->assertTrue($this->service->canTransitionTo(OrderStatusEnum::PROCESSING, OrderStatusEnum::CANCELLED));
    }

    #[Test]
    public function can_transition_to_prevents_transition_from_final_statuses(): void
    {
        // Arrange & Act & Assert
        $this->assertFalse($this->service->canTransitionTo(OrderStatusEnum::DELIVERED, OrderStatusEnum::PROCESSING));
        $this->assertFalse($this->service->canTransitionTo(OrderStatusEnum::CANCELLED, OrderStatusEnum::CONFIRMED));
        $this->assertFalse($this->service->canTransitionTo(OrderStatusEnum::DELIVERED, OrderStatusEnum::CANCELLED));
    }
}