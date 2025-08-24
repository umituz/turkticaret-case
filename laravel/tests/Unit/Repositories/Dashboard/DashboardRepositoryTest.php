<?php

namespace Tests\Unit\Repositories\Dashboard;

use App\Repositories\Dashboard\DashboardRepository;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Models\Product\Product;
use App\Models\User\User;
use App\Enums\Order\OrderStatusEnum;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;

/**
 * Unit tests for DashboardRepository
 * Tests dashboard data retrieval methods with model mocking
 */
#[CoversClass(DashboardRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class DashboardRepositoryTest extends UnitTestCase
{
    private DashboardRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DashboardRepository();
    }

    #[Test]
    public function get_total_users_returns_user_count(): void
    {
        // Arrange
        User::shouldReceive('count')
            ->once()
            ->andReturn(150);

        // Act
        $result = $this->repository->getTotalUsers();

        // Assert
        $this->assertEquals(150, $result);
    }

    #[Test]
    public function get_current_month_users_filters_by_date(): void
    {
        // Arrange
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery = Mockery::mock();
        
        User::shouldReceive('where')
            ->once()
            ->with('created_at', '>=', $currentMonth)
            ->andReturn($mockQuery);
            
        $mockQuery->shouldReceive('count')
            ->once()
            ->andReturn(25);

        // Act
        $result = $this->repository->getCurrentMonthUsers($currentMonth);

        // Assert
        $this->assertEquals(25, $result);
    }

    #[Test]
    public function get_previous_month_users_filters_by_date_range(): void
    {
        // Arrange
        $previousMonth = Carbon::parse('2023-12-01');
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery = Mockery::mock();
        
        User::shouldReceive('whereBetween')
            ->once()
            ->with('created_at', [$previousMonth, $currentMonth])
            ->andReturn($mockQuery);
            
        $mockQuery->shouldReceive('count')
            ->once()
            ->andReturn(20);

        // Act
        $result = $this->repository->getPreviousMonthUsers($previousMonth, $currentMonth);

        // Assert
        $this->assertEquals(20, $result);
    }

    #[Test]
    public function get_current_month_orders_filters_by_date(): void
    {
        // Arrange
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery = Mockery::mock();
        
        Order::shouldReceive('where')
            ->once()
            ->with('created_at', '>=', $currentMonth)
            ->andReturn($mockQuery);
            
        $mockQuery->shouldReceive('count')
            ->once()
            ->andReturn(85);

        // Act
        $result = $this->repository->getCurrentMonthOrders($currentMonth);

        // Assert
        $this->assertEquals(85, $result);
    }

    #[Test]
    public function get_previous_month_orders_filters_by_date_range(): void
    {
        // Arrange
        $previousMonth = Carbon::parse('2023-12-01');
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery = Mockery::mock();
        
        Order::shouldReceive('whereBetween')
            ->once()
            ->with('created_at', [$previousMonth, $currentMonth])
            ->andReturn($mockQuery);
            
        $mockQuery->shouldReceive('count')
            ->once()
            ->andReturn(70);

        // Act
        $result = $this->repository->getPreviousMonthOrders($previousMonth, $currentMonth);

        // Assert
        $this->assertEquals(70, $result);
    }

    #[Test]
    public function get_total_products_returns_product_count(): void
    {
        // Arrange
        Product::shouldReceive('count')
            ->once()
            ->andReturn(500);

        // Act
        $result = $this->repository->getTotalProducts();

        // Assert
        $this->assertEquals(500, $result);
    }

    #[Test]
    public function get_current_month_products_filters_by_date(): void
    {
        // Arrange
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery = Mockery::mock();
        
        Product::shouldReceive('where')
            ->once()
            ->with('created_at', '>=', $currentMonth)
            ->andReturn($mockQuery);
            
        $mockQuery->shouldReceive('count')
            ->once()
            ->andReturn(12);

        // Act
        $result = $this->repository->getCurrentMonthProducts($currentMonth);

        // Assert
        $this->assertEquals(12, $result);
    }

    #[Test]
    public function get_current_month_revenue_sums_delivered_orders(): void
    {
        // Arrange
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        
        Order::shouldReceive('where')
            ->once()
            ->with('created_at', '>=', $currentMonth)
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('where')
            ->once()
            ->with('status', OrderStatusEnum::DELIVERED)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('sum')
            ->once()
            ->with('total_amount')
            ->andReturn(125000.50);

        // Act
        $result = $this->repository->getCurrentMonthRevenue($currentMonth);

        // Assert
        $this->assertEquals(125000.50, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function get_previous_month_revenue_sums_delivered_orders_in_date_range(): void
    {
        // Arrange
        $previousMonth = Carbon::parse('2023-12-01');
        $currentMonth = Carbon::parse('2024-01-01');
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        
        Order::shouldReceive('whereBetween')
            ->once()
            ->with('created_at', [$previousMonth, $currentMonth])
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('where')
            ->once()
            ->with('status', OrderStatusEnum::DELIVERED)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('sum')
            ->once()
            ->with('total_amount')
            ->andReturn(98750.25);

        // Act
        $result = $this->repository->getPreviousMonthRevenue($previousMonth, $currentMonth);

        // Assert
        $this->assertEquals(98750.25, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function get_recent_order_activities_returns_limited_collection(): void
    {
        // Arrange
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        $mockQuery3 = Mockery::mock();
        $expectedCollection = Mockery::mock(Collection::class);
        
        OrderStatusHistory::shouldReceive('with')
            ->once()
            ->with(['order.user'])
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('limit')
            ->once()
            ->with(10)
            ->andReturn($mockQuery3);
            
        $mockQuery3->shouldReceive('get')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->repository->getRecentOrderActivities();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function get_recent_order_activities_respects_custom_limit(): void
    {
        // Arrange
        $customLimit = 5;
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        $mockQuery3 = Mockery::mock();
        $expectedCollection = Mockery::mock(Collection::class);
        
        OrderStatusHistory::shouldReceive('with')
            ->once()
            ->with(['order.user'])
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('limit')
            ->once()
            ->with($customLimit)
            ->andReturn($mockQuery3);
            
        $mockQuery3->shouldReceive('get')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->repository->getRecentOrderActivities($customLimit);

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function get_recent_user_registrations_returns_limited_collection(): void
    {
        // Arrange
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        $expectedCollection = Mockery::mock(Collection::class);
        
        User::shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('limit')
            ->once()
            ->with(3)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('get')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->repository->getRecentUserRegistrations();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function get_recent_product_updates_returns_limited_collection(): void
    {
        // Arrange
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        $expectedCollection = Mockery::mock(Collection::class);
        
        Product::shouldReceive('orderBy')
            ->once()
            ->with('updated_at', 'desc')
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('limit')
            ->once()
            ->with(2)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('get')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->repository->getRecentProductUpdates();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function check_database_status_returns_online_when_connection_succeeds(): void
    {
        // Arrange
        $mockConnection = Mockery::mock();
        $mockPdo = Mockery::mock(\PDO::class);
        
        DB::shouldReceive('connection')
            ->once()
            ->andReturn($mockConnection);
            
        $mockConnection->shouldReceive('getPdo')
            ->once()
            ->andReturn($mockPdo);

        // Act
        $result = $this->repository->checkDatabaseStatus();

        // Assert
        $this->assertEquals('online', $result);
    }

    #[Test]
    public function check_database_status_returns_offline_when_connection_fails(): void
    {
        // Arrange
        $mockConnection = Mockery::mock();
        
        DB::shouldReceive('connection')
            ->once()
            ->andReturn($mockConnection);
            
        $mockConnection->shouldReceive('getPdo')
            ->once()
            ->andThrow(new \Exception('Connection failed'));

        // Act
        $result = $this->repository->checkDatabaseStatus();

        // Assert
        $this->assertEquals('offline', $result);
    }
}