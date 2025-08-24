<?php

namespace Tests\Unit\Services\Shipping;

use App\Services\Shipping\ShippingService;
use App\Repositories\Shipping\ShippingMethodRepositoryInterface;
use App\Models\Shipping\ShippingMethod;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Database\Eloquent\Collection;
use Mockery\MockInterface;

/**
 * Unit tests for ShippingService
 * Tests shipping method operations with proper mocking
 */
#[CoversClass(ShippingService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class ShippingServiceTest extends UnitTestCase
{
    private ShippingService $shippingService;
    private MockInterface $shippingMethodRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->shippingMethodRepository = $this->mock(ShippingMethodRepositoryInterface::class);
        $this->shippingService = new ShippingService($this->shippingMethodRepository);
    }

    #[Test]
    public function constructor_accepts_shipping_method_repository(): void
    {
        // Act
        $service = new ShippingService($this->shippingMethodRepository);

        // Assert
        $this->assertInstanceOf(ShippingService::class, $service);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->shippingService, 'getAvailableMethods'));
        $this->assertTrue(method_exists($this->shippingService, 'getAllMethods'));
        $this->assertTrue(method_exists($this->shippingService, 'getMethodByUuid'));
    }

    #[Test]
    public function get_available_methods_returns_collection(): void
    {
        // Arrange
        $expectedCollection = new Collection([
            $this->mock(ShippingMethod::class),
            $this->mock(ShippingMethod::class)
        ]);

        $this->shippingMethodRepository
            ->shouldReceive('getActiveMethods')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->shippingService->getAvailableMethods();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function get_all_methods_returns_collection(): void
    {
        // Arrange
        $expectedCollection = new Collection([
            $this->mock(ShippingMethod::class),
            $this->mock(ShippingMethod::class),
            $this->mock(ShippingMethod::class)
        ]);

        $this->shippingMethodRepository
            ->shouldReceive('getAllMethods')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->shippingService->getAllMethods();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function get_method_by_uuid_returns_shipping_method_when_found(): void
    {
        // Arrange
        $uuid = $this->generateTestUuid();
        $expectedMethod = $this->mock(ShippingMethod::class);

        $this->shippingMethodRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn($expectedMethod);

        // Act
        $result = $this->shippingService->getMethodByUuid($uuid);

        // Assert
        $this->assertSame($expectedMethod, $result);
    }

    #[Test]
    public function get_method_by_uuid_returns_null_when_not_found(): void
    {
        // Arrange
        $uuid = $this->generateTestUuid();

        $this->shippingMethodRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(null);

        // Act
        $result = $this->shippingService->getMethodByUuid($uuid);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function get_available_methods_calls_repository_active_methods(): void
    {
        // Arrange
        $expectedCollection = new Collection();
        
        $this->shippingMethodRepository
            ->shouldReceive('getActiveMethods')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $this->shippingService->getAvailableMethods();

        // Assert - expectation verified by Mockery
        $this->assertTrue(true);
    }

    #[Test]
    public function get_all_methods_calls_repository_all_methods(): void
    {
        // Arrange
        $expectedCollection = new Collection();
        
        $this->shippingMethodRepository
            ->shouldReceive('getAllMethods')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $this->shippingService->getAllMethods();

        // Assert - expectation verified by Mockery
        $this->assertTrue(true);
    }

    #[Test]
    public function get_method_by_uuid_calls_repository_find_by_uuid(): void
    {
        // Arrange
        $uuid = $this->generateTestUuid();
        
        $this->shippingMethodRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(null);

        // Act
        $this->shippingService->getMethodByUuid($uuid);

        // Assert - expectation verified by Mockery
        $this->assertTrue(true);
    }

    #[Test]
    public function service_methods_return_expected_types(): void
    {
        // Arrange
        $collection = new Collection();
        $uuid = $this->generateTestUuid();
        
        $this->shippingMethodRepository
            ->shouldReceive('getActiveMethods')
            ->once()
            ->andReturn($collection);
            
        $this->shippingMethodRepository
            ->shouldReceive('getAllMethods')
            ->once()
            ->andReturn($collection);
            
        $this->shippingMethodRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(null);

        // Act & Assert
        $this->assertInstanceOf(Collection::class, $this->shippingService->getAvailableMethods());
        $this->assertInstanceOf(Collection::class, $this->shippingService->getAllMethods());
        $this->assertNull($this->shippingService->getMethodByUuid($uuid));
    }

    /**
     * Create test shipping method data
     */
    private function createShippingMethodData(array $overrides = []): array
    {
        return $this->createTestData(array_merge([
            'name' => 'Test Shipping Method',
            'description' => 'Test shipping method description',
            'cost' => 999,
            'active' => true,
            'delivery_time' => '3-5 business days'
        ], $overrides));
    }
}