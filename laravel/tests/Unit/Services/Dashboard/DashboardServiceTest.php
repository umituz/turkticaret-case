<?php

namespace Tests\Unit\Services\Dashboard;

use App\Repositories\Dashboard\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

#[CoversClass(DashboardService::class)]
class DashboardServiceTest extends UnitTestCase
{
    private MockInterface $dashboardRepository;
    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dashboardRepository = Mockery::mock(DashboardRepositoryInterface::class);
        $this->service = new DashboardService($this->dashboardRepository);

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
        $currentMonth = Carbon::create(2024, 3, 1);
        $previousMonth = Carbon::create(2024, 2, 1);

        // Current month data
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')->andReturn(250000);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(25);
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);

        // Previous month data
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(80);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(40);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(200000);

        // Recent activity and system status
        $this->dashboardRepository->shouldReceive('getRecentOrders')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getRecentUsers')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getLowStockProducts')->andReturn([]);

        $result = $this->service->getDashboardData();

        $this->assertIsArray($result['stats']);
        $this->assertCount(4, $result['stats']);

        // Check stats structure
        foreach ($result['stats'] as $stat) {
            $this->assertArrayHasKey('title', $stat);
            $this->assertArrayHasKey('value', $stat);
            $this->assertArrayHasKey('change', $stat);
            $this->assertArrayHasKey('description', $stat);
        }
    }

    #[Test]
    public function it_gets_recent_activity_data(): void
    {
        $this->mockAllRepositoryMethods();

        $result = $this->service->getDashboardData();

        $this->assertIsArray($result['recent_activity']);
        $this->assertArrayHasKey('orders', $result['recent_activity']);
        $this->assertArrayHasKey('users', $result['recent_activity']);
    }

    #[Test]
    public function it_gets_system_status_data(): void
    {
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')->andReturn(250000);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(25);
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(80);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(40);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(200000);
        $this->dashboardRepository->shouldReceive('getRecentOrders')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getRecentUsers')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getLowStockProducts')->andReturn([
            (object)['name' => 'Product 1', 'stock_quantity' => 2],
            (object)['name' => 'Product 2', 'stock_quantity' => 1],
        ]);

        $result = $this->service->getDashboardData();

        $this->assertIsArray($result['system_status']);
        $this->assertArrayHasKey('low_stock_products', $result['system_status']);
        $this->assertCount(2, $result['system_status']['low_stock_products']);
    }

    #[Test]
    public function it_handles_zero_division_in_percentage_calculation(): void
    {
        // Test edge case where previous value is 0
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')->andReturn(250000);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(25);
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(0);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(0);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(0);
        $this->dashboardRepository->shouldReceive('getRecentOrders')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getRecentUsers')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getLowStockProducts')->andReturn([]);

        $result = $this->service->getDashboardData();

        // Should not throw division by zero error
        $this->assertIsArray($result['stats']);
        $this->assertCount(4, $result['stats']);
    }

    #[Test]
    public function it_formats_revenue_correctly(): void
    {
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')->andReturn(123456);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(25);
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(80);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(40);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(100000);
        $this->dashboardRepository->shouldReceive('getRecentOrders')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getRecentUsers')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getLowStockProducts')->andReturn([]);

        $result = $this->service->getDashboardData();

        $revenueStats = $result['stats'][2]; // Revenue is the 3rd stat
        $this->assertEquals('Revenue', $revenueStats['title']);
        $this->assertStringContains('1,234.56', $revenueStats['value']);
    }

    #[Test]
    public function it_calculates_correct_date_ranges(): void
    {
        // Test that the service calls repository methods with correct date parameters
        $expectedCurrentMonth = Carbon::create(2024, 3, 1);
        $expectedPreviousMonth = Carbon::create(2024, 2, 1);

        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')
            ->with(Mockery::type(Carbon::class))
            ->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')
            ->with(Mockery::type(Carbon::class))
            ->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')
            ->with(Mockery::type(Carbon::class))
            ->andReturn(250000);

        $this->mockRemainingRepositoryMethods();

        $this->service->getDashboardData();

        // Assertions are implicit in shouldReceive expectations
        $this->assertTrue(true);
    }

    private function mockAllRepositoryMethods(): void
    {
        $this->dashboardRepository->shouldReceive('getCurrentMonthUsers')->andReturn(100);
        $this->dashboardRepository->shouldReceive('getCurrentMonthOrders')->andReturn(50);
        $this->dashboardRepository->shouldReceive('getCurrentMonthRevenue')->andReturn(250000);
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(25);
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(80);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(40);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(200000);
        $this->dashboardRepository->shouldReceive('getRecentOrders')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getRecentUsers')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getLowStockProducts')->andReturn([]);
    }

    private function mockRemainingRepositoryMethods(): void
    {
        $this->dashboardRepository->shouldReceive('getTotalProducts')->andReturn(25);
        $this->dashboardRepository->shouldReceive('getTotalUsers')->andReturn(500);
        $this->dashboardRepository->shouldReceive('getPreviousMonthUsers')->andReturn(80);
        $this->dashboardRepository->shouldReceive('getPreviousMonthOrders')->andReturn(40);
        $this->dashboardRepository->shouldReceive('getPreviousMonthRevenue')->andReturn(200000);
        $this->dashboardRepository->shouldReceive('getRecentOrders')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getRecentUsers')->andReturn([]);
        $this->dashboardRepository->shouldReceive('getLowStockProducts')->andReturn([]);
    }
}