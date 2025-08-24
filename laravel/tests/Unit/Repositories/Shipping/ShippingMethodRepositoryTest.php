<?php

namespace Tests\Unit\Repositories\Shipping;

use App\Repositories\Shipping\ShippingMethodRepository;
use App\Repositories\Shipping\ShippingMethodRepositoryInterface;
use App\Models\Shipping\ShippingMethod;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for ShippingMethodRepository
 * Tests repository structure and interface implementation
 */
#[CoversClass(ShippingMethodRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class ShippingMethodRepositoryTest extends UnitTestCase
{
    private ShippingMethodRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ShippingMethodRepository(new ShippingMethod());
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        // Act
        $repository = new ShippingMethodRepository(new ShippingMethod());

        // Assert
        $this->assertInstanceOf(ShippingMethodRepository::class, $repository);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'getActiveMethods'));
        $this->assertTrue(method_exists($this->repository, 'getAllMethods'));
        $this->assertTrue(method_exists($this->repository, 'findByUuid'));
    }

    #[Test]
    public function it_extends_base_repository(): void
    {
        // Assert
        $this->assertInstanceOf(\App\Repositories\Base\BaseRepository::class, $this->repository);
    }

    #[Test]
    public function it_implements_interface(): void
    {
        // Assert
        $this->assertInstanceOf(ShippingMethodRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function it_uses_correct_model(): void
    {
        // Act
        $model = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(ShippingMethod::class, $model);
    }

    #[Test]
    public function repository_constructor_accepts_model(): void
    {
        // Arrange
        $model = new ShippingMethod();

        // Act
        $repository = new ShippingMethodRepository($model);

        // Assert
        $this->assertInstanceOf(ShippingMethodRepository::class, $repository);
    }

    #[Test]
    public function repository_inherits_base_functionality(): void
    {
        // Assert - test inherited methods from BaseRepository
        $this->assertTrue(method_exists($this->repository, 'find'));
        $this->assertTrue(method_exists($this->repository, 'create'));
        $this->assertTrue(method_exists($this->repository, 'update'));
        $this->assertTrue(method_exists($this->repository, 'delete'));
        $this->assertTrue(method_exists($this->repository, 'paginate'));
    }

    #[Test]
    public function repository_works_with_shipping_method_model(): void
    {
        // Assert
        $this->assertInstanceOf(ShippingMethod::class, $this->repository->getModel());
        $this->assertEquals(ShippingMethod::class, get_class($this->repository->getModel()));
    }

    #[Test]
    public function repository_maintains_model_relationship(): void
    {
        // Arrange
        $model = new ShippingMethod();
        $repository = new ShippingMethodRepository($model);

        // Act
        $repositoryModel = $repository->getModel();

        // Assert
        $this->assertSame($model, $repositoryModel);
    }

    #[Test]
    public function repository_follows_repository_pattern(): void
    {
        // Assert
        $this->assertInstanceOf(ShippingMethodRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(\App\Repositories\Base\BaseRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function repository_supports_shipping_method_operations(): void
    {
        // Assert - shipping method specific methods
        $this->assertTrue(method_exists($this->repository, 'getActiveMethods'));
        $this->assertTrue(method_exists($this->repository, 'getAllMethods'));
    }

    #[Test]
    public function repository_handles_shipping_method_specific_operations(): void
    {
        // Assert - check method signatures exist (cannot test functionality in unit test without database)
        $reflection = new \ReflectionClass($this->repository);
        
        $this->assertTrue($reflection->hasMethod('getActiveMethods'));
        $this->assertTrue($reflection->hasMethod('getAllMethods'));
        $this->assertTrue($reflection->hasMethod('findByUuid'));
    }

    #[Test]
    public function repository_has_proper_method_visibility(): void
    {
        // Assert - check public methods are accessible
        $reflection = new \ReflectionClass($this->repository);
        
        $getActiveMethodsMethod = $reflection->getMethod('getActiveMethods');
        $getAllMethodsMethod = $reflection->getMethod('getAllMethods');
        $findByUuidMethod = $reflection->getMethod('findByUuid');
        
        $this->assertTrue($getActiveMethodsMethod->isPublic());
        $this->assertTrue($getAllMethodsMethod->isPublic());
        $this->assertTrue($findByUuidMethod->isPublic());
    }
}