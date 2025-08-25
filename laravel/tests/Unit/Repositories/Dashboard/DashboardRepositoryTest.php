<?php

namespace Tests\Unit\Repositories\Dashboard;

use App\Repositories\Dashboard\DashboardRepository;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Models\Product\Product;
use App\Models\User\User;
use App\Enums\Order\OrderStatusEnum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integration tests for DashboardRepository
 * Tests dashboard data retrieval methods with real database
 */
#[CoversClass(DashboardRepository::class)]
#[Group('unit')]
#[Group('repositories')]
class DashboardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DashboardRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DashboardRepository();
    }

    #[Test]
    public function it_returns_total_users_count(): void
    {
        User::factory()->count(5)->create();

        $result = $this->repository->getTotalUsers();

        $this->assertEquals(5, $result);
    }

    #[Test]
    public function it_filters_current_month_users_by_date(): void
    {
        $currentMonth = Carbon::parse('2024-01-01');
        
        User::factory()->count(3)->create(['created_at' => $currentMonth->copy()->addDays(5)]);
        User::factory()->count(2)->create(['created_at' => $currentMonth->copy()->subDays(5)]);

        $result = $this->repository->getCurrentMonthUsers($currentMonth);

        $this->assertEquals(3, $result);
    }

    #[Test]
    public function it_filters_previous_month_users_by_date_range(): void
    {
        $previousMonth = Carbon::parse('2023-12-01');
        $currentMonth = Carbon::parse('2024-01-01');
        
        User::factory()->count(2)->create(['created_at' => $previousMonth->copy()->addDays(10)]);
        User::factory()->count(3)->create(['created_at' => $currentMonth->copy()->addDays(5)]);
        User::factory()->count(1)->create(['created_at' => $previousMonth->copy()->subDays(5)]);

        $result = $this->repository->getPreviousMonthUsers($previousMonth, $currentMonth);

        $this->assertEquals(2, $result);
    }

    #[Test]
    public function it_filters_current_month_orders_by_date(): void
    {
        $currentMonth = Carbon::parse('2024-01-01');
        
        Order::factory()->count(4)->create(['created_at' => $currentMonth->copy()->addDays(5)]);
        Order::factory()->count(2)->create(['created_at' => $currentMonth->copy()->subDays(5)]);

        $result = $this->repository->getCurrentMonthOrders($currentMonth);

        $this->assertEquals(4, $result);
    }

    #[Test]
    public function it_filters_previous_month_orders_by_date_range(): void
    {
        $previousMonth = Carbon::parse('2023-12-01');
        $currentMonth = Carbon::parse('2024-01-01');
        
        Order::factory()->count(3)->create(['created_at' => $previousMonth->copy()->addDays(10)]);
        Order::factory()->count(2)->create(['created_at' => $currentMonth->copy()->addDays(5)]);
        Order::factory()->count(1)->create(['created_at' => $previousMonth->copy()->subDays(5)]);

        $result = $this->repository->getPreviousMonthOrders($previousMonth, $currentMonth);

        $this->assertEquals(3, $result);
    }

    #[Test]
    public function it_returns_total_products_count(): void
    {
        Product::factory()->count(10)->create();

        $result = $this->repository->getTotalProducts();

        $this->assertEquals(10, $result);
    }

    #[Test]
    public function it_filters_current_month_products_by_date(): void
    {
        $currentMonth = Carbon::parse('2024-01-01');
        
        Product::factory()->count(6)->create(['created_at' => $currentMonth->copy()->addDays(5)]);
        Product::factory()->count(3)->create(['created_at' => $currentMonth->copy()->subDays(5)]);

        $result = $this->repository->getCurrentMonthProducts($currentMonth);

        $this->assertEquals(6, $result);
    }

    #[Test]
    public function it_sums_current_month_revenue_from_delivered_orders(): void
    {
        $currentMonth = Carbon::parse('2024-01-01');
        
        Order::factory()->count(2)->create([
            'created_at' => $currentMonth->copy()->addDays(5),
            'status' => OrderStatusEnum::DELIVERED,
            'total_amount' => 50000
        ]);
        
        Order::factory()->count(1)->create([
            'created_at' => $currentMonth->copy()->addDays(5),
            'status' => OrderStatusEnum::PENDING,
            'total_amount' => 30000
        ]);
        
        Order::factory()->count(1)->create([
            'created_at' => $currentMonth->copy()->subDays(5),
            'status' => OrderStatusEnum::DELIVERED,
            'total_amount' => 25000
        ]);

        $result = $this->repository->getCurrentMonthRevenue($currentMonth);

        $this->assertEquals(100000.0, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function it_sums_previous_month_revenue_from_delivered_orders_in_date_range(): void
    {
        $previousMonth = Carbon::parse('2023-12-01');
        $currentMonth = Carbon::parse('2024-01-01');
        
        Order::factory()->count(1)->create([
            'created_at' => $previousMonth->copy()->addDays(10),
            'status' => OrderStatusEnum::DELIVERED,
            'total_amount' => 75000
        ]);
        
        Order::factory()->count(1)->create([
            'created_at' => $currentMonth->copy()->addDays(5),
            'status' => OrderStatusEnum::DELIVERED,
            'total_amount' => 40000
        ]);
        
        Order::factory()->count(1)->create([
            'created_at' => $previousMonth->copy()->subDays(5),
            'status' => OrderStatusEnum::DELIVERED,
            'total_amount' => 20000
        ]);

        $result = $this->repository->getPreviousMonthRevenue($previousMonth, $currentMonth);

        $this->assertEquals(75000.0, $result);
        $this->assertIsFloat($result);
    }

    #[Test]
    public function it_returns_limited_collection_of_recent_order_activities(): void
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(5)->create(['user_uuid' => $user->uuid]);
        
        foreach ($orders as $order) {
            OrderStatusHistory::create([
                'uuid' => fake()->uuid(),
                'order_uuid' => $order->uuid,
                'new_status' => OrderStatusEnum::DELIVERED,
                'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
                'updated_at' => Carbon::now()
            ]);
        }
        
        for ($i = 0; $i < 8; $i++) {
            $order = Order::factory()->create();
            OrderStatusHistory::create([
                'uuid' => fake()->uuid(),
                'order_uuid' => $order->uuid,
                'new_status' => OrderStatusEnum::PENDING,
                'created_at' => Carbon::now()->subMinutes(rand(1, 120)),
                'updated_at' => Carbon::now()
            ]);
        }

        $result = $this->repository->getRecentOrderActivities();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(10, $result);
    }

    #[Test]
    public function it_respects_custom_limit_for_recent_order_activities(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $order = Order::factory()->create();
            OrderStatusHistory::create([
                'uuid' => fake()->uuid(),
                'order_uuid' => $order->uuid,
                'new_status' => OrderStatusEnum::DELIVERED,
                'created_at' => Carbon::now()->subMinutes(rand(1, 120)),
                'updated_at' => Carbon::now()
            ]);
        }

        $result = $this->repository->getRecentOrderActivities(5);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(5, $result);
    }

    #[Test]
    public function it_returns_limited_collection_of_recent_user_registrations(): void
    {
        User::factory()->count(5)->create([
            'created_at' => Carbon::now()->subMinutes(rand(1, 120))
        ]);

        $result = $this->repository->getRecentUserRegistrations();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
    }

    #[Test]
    public function it_returns_limited_collection_of_recent_product_updates(): void
    {
        Product::factory()->count(4)->create([
            'updated_at' => Carbon::now()->subMinutes(rand(1, 120))
        ]);

        $result = $this->repository->getRecentProductUpdates();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_returns_online_status_when_database_connection_succeeds(): void
    {
        $result = $this->repository->checkDatabaseStatus();

        $this->assertEquals('online', $result);
    }

    #[Test]
    public function it_returns_offline_status_when_database_connection_fails(): void
    {
        DB::shouldReceive('connection')
            ->andThrow(new \Exception('Connection failed'));

        $result = $this->repository->checkDatabaseStatus();

        $this->assertEquals('offline', $result);
    }
}