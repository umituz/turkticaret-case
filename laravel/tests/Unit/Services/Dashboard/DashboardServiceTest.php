<?php

namespace Tests\Unit\Services\Dashboard;

use App\Models\Order\OrderStatusHistory;
use App\Models\Product\Product;
use App\Models\User\User;
use App\Repositories\Dashboard\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseServiceUnitTest;

#[CoversClass(DashboardService::class)]
class DashboardServiceTest extends BaseServiceUnitTest
{
    private MockInterface $dashboardRepository;

    protected function getServiceClass(): string
    {
        return DashboardService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->dashboardRepository = $this->mock(DashboardRepositoryInterface::class);

        return [
            $this->dashboardRepository,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Carbon::now() to return a consistent date for testing
        Carbon::setTestNow(Carbon::create(2024, 3, 15, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon test time
        parent::tearDown();
    }

    #[Test]
    public function it_gets_dashboard_data_with_all_sections(): void
    {
        $this->mockAllRepositoryMethods();

        $result = $this->service->getDashboardData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('recent_activity', $result);
        $this->assertArrayHasKey('system_status', $result);
    }

    #[Test]
    public function it_calculates_stats_with_current_and_previous_month_data(): void
    {
        $currentMonth = Carbon::create(2024, 3, 1); // March 2024
        $previousMonth = Carbon::create(2024, 2, 1); // February 2024

        // Current month data
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')
            ->with(Mockery::on(function ($date) use ($currentMonth) {
                return $date->format('Y-m-d') === $currentMonth->format('Y-m-d');
            }))
            ->andReturn(100);

        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')
            ->with(Mockery::on(function ($date) use ($currentMonth) {
                return $date->format('Y-m-d') === $currentMonth->format('Y-m-d');
            }))
            ->andReturn(50);

        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')
            ->with(Mockery::on(function ($date) use ($currentMonth) {
                return $date->format('Y-m-d') === $currentMonth->format('Y-m-d');
            }))
            ->andReturn(250000); // 2,500.00

        $this->dashboardRepository->shouldReceive('getCurrentMonthProducts')
            ->with(Mockery::on(function ($date) use ($currentMonth) {
                return $date->format('Y-m-d') === $currentMonth->format('Y-m-d');
            }))
            ->andReturn(5);

        // Previous month data
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')
            ->with(
                Mockery::on(function ($date) use ($previousMonth) {
                    return $date->format('Y-m-d') === $previousMonth->format('Y-m-d');
                }),
                Mockery::on(function ($date) use ($currentMonth) {
                    return $date->format('Y-m-d') === $currentMonth->format('Y-m-d');
                })
            )
            ->andReturn(80);

        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')
            ->andReturn(40);

        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')
            ->andReturn(200000); // 2,000.00

        // Total data
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(1000);

        // Mock other required methods
        $this->mockRecentActivityMethods();
        $this->mockSystemStatusMethods();

        $result = $this->service->getDashboardData();

        $stats = $result['stats'];

        $this->assertCount(4, $stats);

        // Users stat
        $this->assertEquals('Total Users', $stats[0]['title']);
        $this->assertEquals('500', $stats[0]['value']);
        $this->assertEquals('+25.0%', $stats[0]['change']); // (100-80)/80 * 100 = 25%
        $this->assertEquals('Active users this month', $stats[0]['description']);

        // Orders stat
        $this->assertEquals('Orders', $stats[1]['title']);
        $this->assertEquals('50', $stats[1]['value']);
        $this->assertEquals('+25.0%', $stats[1]['change']); // (50-40)/40 * 100 = 25%
        $this->assertEquals('Orders completed this month', $stats[1]['description']);

        // Products stat
        $this->assertEquals('Products', $stats[2]['title']);
        $this->assertEquals('1,000', $stats[2]['value']);
        $this->assertEquals('+5', $stats[2]['change']);
        $this->assertEquals('Total products in inventory', $stats[2]['description']);

        // Revenue stat
        $this->assertEquals('Revenue', $stats[3]['title']);
        $this->assertEquals('2,500.00 ₺', $stats[3]['value']);
        $this->assertEquals('+25.0%', $stats[3]['change']); // (250000-200000)/200000 * 100 = 25%
        $this->assertEquals('Total revenue this month', $stats[3]['description']);
    }

    #[Test]
    #[DataProvider('percentageChangeDataProvider')]
    public function it_calculates_percentage_change_correctly(int|float $current, int|float $previous, string $expected): void
    {
        // Use reflection to access the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculatePercentageChange');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $current, $previous);

        $this->assertEquals($expected, $result);
    }

    public static function percentageChangeDataProvider(): array
    {
        return [
            'Positive increase' => [120, 100, '+20.0%'],
            'Negative decrease' => [80, 100, '-20.0%'],
            'No change' => [100, 100, '+0.0%'],
            'From zero to value' => [50, 0, '+100%'],
            'Both zero' => [0, 0, '0%'],
            'Small decimal increase' => [101.5, 100, '+1.5%'],
            'Large increase' => [300, 100, '+200.0%'],
            'Large decrease' => [25, 100, '-75.0%'],
            'Float values' => [123.45, 100.5, '+22.8%'],
        ];
    }

    #[Test]
    public function it_gets_recent_activity_with_mixed_activities(): void
    {
        $orderHistory = $this->createMockOrderHistory();
        $userRegistration = $this->createMockUser();
        $productUpdate = $this->createMockProduct();

        $this->dashboardRepository->shouldReceive('getRecentOrderActivities')
            ->andReturn(collect([$orderHistory]));

        $this->dashboardRepository->shouldReceive('getRecentUserRegistrations')
            ->andReturn(collect([$userRegistration]));

        $this->dashboardRepository->shouldReceive('getRecentProductUpdates')
            ->andReturn(collect([$productUpdate]));

        // Mock other required methods
        $this->mockStatsCalculation();
        $this->mockSystemStatusMethods();

        $result = $this->service->getDashboardData();

        $activities = $result['recent_activity'];

        $this->assertIsArray($activities);
        $this->assertCount(3, $activities);

        // Check that activities contain expected types
        $activityTypes = array_column($activities, 'type');
        $this->assertContains('order', $activityTypes);
        $this->assertContains('user', $activityTypes);
        $this->assertContains('product', $activityTypes);
    }

    #[Test]
    public function it_formats_order_activity_for_pending_status(): void
    {
        $orderHistory = $this->createMockOrderHistory('pending', null);

        $this->dashboardRepository->shouldReceive('getRecentOrderActivities')
            ->andReturn(collect([$orderHistory]));

        $this->dashboardRepository->shouldReceive('getRecentUserRegistrations')
            ->andReturn(collect([]));

        $this->dashboardRepository->shouldReceive('getRecentProductUpdates')
            ->andReturn(collect([]));

        $this->mockStatsCalculation();
        $this->mockSystemStatusMethods();

        $result = $this->service->getDashboardData();

        $activities = $result['recent_activity'];
        $orderActivity = array_filter($activities, fn($activity) => $activity['type'] === 'order')[0];

        $this->assertEquals('info', $orderActivity['status']);
        $this->assertStringContains('Test User placed a new order #ORD-123', $orderActivity['message']);
    }

    #[Test]
    public function it_formats_order_activity_for_confirmed_status(): void
    {
        $orderHistory = $this->createMockOrderHistory('confirmed', 'pending');

        $this->dashboardRepository->shouldReceive('getRecentOrderActivities')
            ->andReturn(collect([$orderHistory]));

        $this->dashboardRepository->shouldReceive('getRecentUserRegistrations')
            ->andReturn(collect([]));

        $this->dashboardRepository->shouldReceive('getRecentProductUpdates')
            ->andReturn(collect([]));

        $this->mockStatsCalculation();
        $this->mockSystemStatusMethods();

        $result = $this->service->getDashboardData();

        $activities = $result['recent_activity'];
        $orderActivity = array_filter($activities, fn($activity) => $activity['type'] === 'order')[0];

        $this->assertEquals('success', $orderActivity['status']);
        $this->assertStringContains('Order #ORD-123 was confirmed by Admin User', $orderActivity['message']);
    }

    #[Test]
    public function it_formats_order_activity_for_cancelled_status(): void
    {
        $orderHistory = $this->createMockOrderHistory('cancelled', 'pending');

        $this->dashboardRepository->shouldReceive('getRecentOrderActivities')
            ->andReturn(collect([$orderHistory]));

        $this->dashboardRepository->shouldReceive('getRecentUserRegistrations')
            ->andReturn(collect([]));

        $this->dashboardRepository->shouldReceive('getRecentProductUpdates')
            ->andReturn(collect([]));

        $this->mockStatsCalculation();
        $this->mockSystemStatusMethods();

        $result = $this->service->getDashboardData();

        $activities = $result['recent_activity'];
        $orderActivity = array_filter($activities, fn($activity) => $activity['type'] === 'order')[0];

        $this->assertEquals('warning', $orderActivity['status']);
        $this->assertStringContains('Order #ORD-123 was cancelled by Admin User', $orderActivity['message']);
    }

    #[Test]
    public function it_gets_system_status_with_all_components(): void
    {
        $this->dashboardRepository->shouldReceive('checkDatabaseStatus')
            ->andReturn('online');

        $this->mockStatsCalculation();
        $this->mockRecentActivityMethods();

        $result = $this->service->getDashboardData();

        $systemStatus = $result['system_status'];

        $this->assertIsArray($systemStatus);
        $this->assertCount(5, $systemStatus);

        $statusIds = array_column($systemStatus, 'id');
        $this->assertContains('server', $statusIds);
        $this->assertContains('database', $statusIds);
        $this->assertContains('backup', $statusIds);
        $this->assertContains('storage', $statusIds);
        $this->assertContains('security', $statusIds);

        // Check database status is set correctly
        $databaseStatus = array_filter($systemStatus, fn($status) => $status['id'] === 'database')[0];
        $this->assertEquals('online', $databaseStatus['status']);
    }

    #[Test]
    public function it_gets_daily_sales_report_with_default_date(): void
    {
        $expectedData = [
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'total_orders' => 25,
            'total_revenue' => 125000,
            'average_order_value' => 5000,
        ];

        $this->dashboardRepository->shouldReceive('getDailySalesReport')
            ->with(Mockery::on(function ($date) {
                return $date->isYesterday();
            }))
            ->andReturn($expectedData);

        $result = $this->service->getDailySalesReport();

        $this->assertEquals($expectedData, $result);
    }

    #[Test]
    public function it_gets_daily_sales_report_with_custom_date(): void
    {
        $customDate = '2024-03-10';
        $expectedCarbon = Carbon::createFromFormat('Y-m-d', $customDate);

        $expectedData = [
            'date' => $customDate,
            'total_orders' => 30,
            'total_revenue' => 150000,
            'average_order_value' => 5000,
        ];

        $this->dashboardRepository->shouldReceive('getDailySalesReport')
            ->with(Mockery::on(function ($date) use ($expectedCarbon) {
                return $date->format('Y-m-d') === $expectedCarbon->format('Y-m-d');
            }))
            ->andReturn($expectedData);

        $result = $this->service->getDailySalesReport($customDate);

        $this->assertEquals($expectedData, $result);
    }

    #[Test]
    public function it_handles_storage_status_calculations(): void
    {
        // Mock disk space functions through reflection or by mocking filesystem
        $reflection = new \ReflectionClass($this->service);

        $getStorageStatusMethod = $reflection->getMethod('getStorageStatus');
        $getStorageStatusMethod->setAccessible(true);

        $getStorageUsageMethod = $reflection->getMethod('getStorageUsage');
        $getStorageUsageMethod->setAccessible(true);

        // Create a partial mock to override getStorageUsagePercentage
        $serviceMock = Mockery::mock($this->service)->makePartial();
        $serviceMock->shouldReceive('getStorageUsagePercentage')->andReturn(50);

        // Test various storage usage levels
        $this->assertEquals('online', $getStorageStatusMethod->invoke($serviceMock));
        $this->assertEquals('50% used', $getStorageUsageMethod->invoke($serviceMock));

        // Test warning level (75-89%)
        $serviceMock->shouldReceive('getStorageUsagePercentage')->andReturn(80);
        $this->assertEquals('warning', $getStorageStatusMethod->invoke($serviceMock));

        // Test critical level (90%+)
        $serviceMock->shouldReceive('getStorageUsagePercentage')->andReturn(95);
        $this->assertEquals('offline', $getStorageStatusMethod->invoke($serviceMock));
    }

    #[Test]
    public function it_limits_recent_activity_to_ten_items(): void
    {
        // Create 15 mock activities (5 each type)
        $orderHistories = collect(range(1, 5))->map(fn($i) => $this->createMockOrderHistory('pending', null, "ORD-{$i}"));
        $userRegistrations = collect(range(1, 5))->map(fn($i) => $this->createMockUser("User {$i}"));
        $productUpdates = collect(range(1, 5))->map(fn($i) => $this->createMockProduct("Product {$i}"));

        $this->dashboardRepository->shouldReceive('getRecentOrderActivities')
            ->andReturn($orderHistories);

        $this->dashboardRepository->shouldReceive('getRecentUserRegistrations')
            ->andReturn($userRegistrations);

        $this->dashboardRepository->shouldReceive('getRecentProductUpdates')
            ->andReturn($productUpdates);

        $this->mockStatsCalculation();
        $this->mockSystemStatusMethods();

        $result = $this->service->getDashboardData();

        $activities = $result['recent_activity'];

        // Should be limited to 10 items even though we have 15
        $this->assertCount(10, $activities);
    }

    private function mockAllRepositoryMethods(): void
    {
        $this->mockStatsCalculation();
        $this->mockRecentActivityMethods();
        $this->mockSystemStatusMethods();
    }

    private function mockStatsCalculation(): void
    {
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')->andReturn(250000);
        $this->dashboardRepository->shouldReceive('getCurrentMonthProducts')->andReturn(5);

        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(80);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(40);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(200000);

        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(1000);
    }

    private function mockRecentActivityMethods(): void
    {
        $this->dashboardRepository->shouldReceive('getRecentOrderActivities')
            ->andReturn(collect([]));

        $this->dashboardRepository->shouldReceive('getRecentUserRegistrations')
            ->andReturn(collect([]));

        $this->dashboardRepository->shouldReceive('getRecentProductUpdates')
            ->andReturn(collect([]));
    }

    private function mockSystemStatusMethods(): void
    {
        $this->dashboardRepository->shouldReceive('checkDatabaseStatus')
            ->andReturn('online');
    }

    private function createMockOrderHistory(string $newStatus = 'pending', ?string $oldStatus = null, string $orderNumber = 'ORD-123'): MockInterface
    {
        $orderHistory = Mockery::mock(OrderStatusHistory::class);
        $orderHistory->uuid = 'test-history-uuid';
        $orderHistory->created_at = Carbon::now()->subMinutes(10);

        // Mock the order relationship
        $order = Mockery::mock();
        $order->order_number = $orderNumber;
        $orderHistory->shouldReceive('getAttribute')->with('order')->andReturn($order);

        // Mock the user relationship through order
        $user = Mockery::mock();
        $user->name = 'Test User';
        $order->shouldReceive('getAttribute')->with('user')->andReturn($user);

        // Mock the changedBy relationship
        $changedBy = Mockery::mock();
        $changedBy->name = 'Admin User';
        $orderHistory->shouldReceive('getAttribute')->with('changedBy')->andReturn($changedBy);

        // Mock status enums
        $oldStatusEnum = $oldStatus ? Mockery::mock() : null;
        if ($oldStatusEnum) {
            $oldStatusEnum->shouldReceive('getAttribute')->with('value')->andReturn($oldStatus);
        }

        $newStatusEnum = Mockery::mock();
        $newStatusEnum->shouldReceive('getAttribute')->with('value')->andReturn($newStatus);

        $orderHistory->shouldReceive('getAttribute')->with('old_status')->andReturn($oldStatusEnum);
        $orderHistory->shouldReceive('getAttribute')->with('new_status')->andReturn($newStatusEnum);

        return $orderHistory;
    }

    private function createMockUser(string $name = 'Test User'): MockInterface
    {
        $user = Mockery::mock(User::class);
        $user->uuid = 'test-user-uuid';
        $user->name = $name;
        $user->created_at = Carbon::now()->subMinutes(5);

        return $user;
    }

    private function createMockProduct(string $name = 'Test Product'): MockInterface
    {
        $product = Mockery::mock(Product::class);
        $product->uuid = 'test-product-uuid';
        $product->name = $name;
        $product->updated_at = Carbon::now()->subMinutes(15);

        return $product;
    }
}
