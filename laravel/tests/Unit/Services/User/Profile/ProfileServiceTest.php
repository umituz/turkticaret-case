<?php

namespace Tests\Unit\Services\User\Profile;

use App\Services\User\Profile\ProfileService;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Models\User\User;
use App\DTOs\Profile\ProfileUpdateDTO;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Mockery;

/**
 * Unit tests for ProfileService
 * Tests user profile management with repository mocking
 */
#[CoversClass(ProfileService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class ProfileServiceTest extends UnitTestCase
{
    private ProfileService $service;
    private UserRepositoryInterface $mockUserRepository;
    private OrderRepositoryInterface $mockOrderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockUserRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->mockOrderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->service = new ProfileService($this->mockUserRepository, $this->mockOrderRepository);
    }

    #[Test]
    public function get_profile_returns_user(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);

        // Act
        $result = $this->service->getProfile($user);

        // Assert
        $this->assertSame($user, $result);
    }

    #[Test]
    public function update_profile_updates_user_and_returns_fresh_instance(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')
            ->with('uuid')
            ->andReturn('test-user-uuid');
        
        $data = ['first_name' => 'John', 'last_name' => 'Doe'];
        $updatedUser = Mockery::mock(User::class);
        
        // Mock DTO creation and conversion
        ProfileUpdateDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn(Mockery::mock(ProfileUpdateDTO::class, [
                'toArray' => $data
            ]));

        $this->mockUserRepository->shouldReceive('updateByUuid')
            ->once()
            ->with('test-user-uuid', $data);
            
        $this->mockUserRepository->shouldReceive('findByUuid')
            ->once()
            ->with('test-user-uuid')
            ->andReturn($updatedUser);

        // Act
        $result = $this->service->updateProfile($user, $data);

        // Assert
        $this->assertSame($updatedUser, $result);
    }

    #[Test]
    public function get_user_stats_returns_empty_stats_for_user_with_no_orders(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $user->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-01'));
        
        $emptyOrders = new Collection([]);
        
        $this->mockOrderRepository->shouldReceive('findByUserId')
            ->once()
            ->with(1)
            ->andReturn($emptyOrders);

        // Act
        $result = $this->service->getUserStats($user);

        // Assert
        $this->assertEquals([
            'total_orders' => 0,
            'total_spent' => 0,
            'average_order_value' => 0,
            'member_since' => '2024-01-01T00:00:00.000Z',
            'last_order' => null,
        ], $result);
    }

    #[Test]
    public function get_user_stats_calculates_correct_statistics_with_orders(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $user->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-01'));
        
        $order1 = Mockery::mock();
        $order1->total = 100.50;
        $order1->order_number = 'ORD-001';
        $order1->status = 'delivered';
        $order1->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-15'));
        
        $order2 = Mockery::mock();
        $order2->total = 75.25;
        $order2->order_number = 'ORD-002';
        $order2->status = 'pending';
        $order2->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-20'));
        
        $orders = new Collection([$order1, $order2]);
        
        $this->mockOrderRepository->shouldReceive('findByUserId')
            ->once()
            ->with(1)
            ->andReturn($orders);

        // Act
        $result = $this->service->getUserStats($user);

        // Assert
        $this->assertEquals([
            'total_orders' => 2,
            'total_spent' => 175.75,
            'average_order_value' => 87.875,
            'member_since' => '2024-01-01T00:00:00.000Z',
            'last_order' => [
                'order_number' => 'ORD-002',
                'total' => 75.25,
                'status' => 'pending',
                'created_at' => '2024-01-20T00:00:00.000Z',
            ],
        ], $result);
    }

    #[Test]
    public function get_user_stats_handles_single_order_correctly(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $user->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-01'));
        
        $order = Mockery::mock();
        $order->total = 150.00;
        $order->order_number = 'ORD-001';
        $order->status = 'completed';
        $order->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-15'));
        
        $orders = new Collection([$order]);
        
        $this->mockOrderRepository->shouldReceive('findByUserId')
            ->once()
            ->with(1)
            ->andReturn($orders);

        // Act
        $result = $this->service->getUserStats($user);

        // Assert
        $this->assertEquals([
            'total_orders' => 1,
            'total_spent' => 150.00,
            'average_order_value' => 150.00,
            'member_since' => '2024-01-01T00:00:00.000Z',
            'last_order' => [
                'order_number' => 'ORD-001',
                'total' => 150.00,
                'status' => 'completed',
                'created_at' => '2024-01-15T00:00:00.000Z',
            ],
        ], $result);
    }

    #[Test]
    public function get_user_stats_sorts_orders_by_created_at_desc_for_last_order(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $user->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-01'));
        
        $olderOrder = Mockery::mock();
        $olderOrder->total = 100.00;
        $olderOrder->order_number = 'ORD-001';
        $olderOrder->status = 'delivered';
        $olderOrder->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-10'));
        
        $newerOrder = Mockery::mock();
        $newerOrder->total = 200.00;
        $newerOrder->order_number = 'ORD-002';
        $newerOrder->status = 'pending';
        $newerOrder->shouldReceive('getAttribute')
            ->with('created_at')
            ->andReturn(Carbon::parse('2024-01-20'));
        
        $orders = new Collection([$olderOrder, $newerOrder]);
        
        $this->mockOrderRepository->shouldReceive('findByUserId')
            ->once()
            ->with(1)
            ->andReturn($orders);

        // Act
        $result = $this->service->getUserStats($user);

        // Assert - Should show the newer order as last_order
        $this->assertEquals('ORD-002', $result['last_order']['order_number']);
        $this->assertEquals(200.00, $result['last_order']['total']);
        $this->assertEquals('pending', $result['last_order']['status']);
        $this->assertEquals('2024-01-20T00:00:00.000Z', $result['last_order']['created_at']);
    }
}