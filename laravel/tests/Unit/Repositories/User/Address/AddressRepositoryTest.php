<?php

namespace Tests\Unit\Repositories\User\Address;

use App\Repositories\User\Address\AddressRepository;
use App\Repositories\User\Address\AddressRepositoryInterface;
use App\Models\User\UserAddress;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for AddressRepository
 * Tests repository structure and interface implementation
 */
#[CoversClass(AddressRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class AddressRepositoryTest extends UnitTestCase
{
    private AddressRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AddressRepository(new UserAddress());
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        // Act
        $repository = new AddressRepository(new UserAddress());

        // Assert
        $this->assertInstanceOf(AddressRepository::class, $repository);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByUser'));
        $this->assertTrue(method_exists($this->repository, 'unsetDefaultAddresses'));
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
        $this->assertInstanceOf(AddressRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function it_uses_correct_model(): void
    {
        // Act
        $model = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(UserAddress::class, $model);
    }

    #[Test]
    public function repository_constructor_accepts_model(): void
    {
        // Arrange
        $model = new UserAddress();

        // Act
        $repository = new AddressRepository($model);

        // Assert
        $this->assertInstanceOf(AddressRepository::class, $repository);
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
    public function repository_works_with_user_address_model(): void
    {
        // Assert
        $this->assertInstanceOf(UserAddress::class, $this->repository->getModel());
        $this->assertEquals(UserAddress::class, get_class($this->repository->getModel()));
    }

    #[Test]
    public function repository_maintains_model_relationship(): void
    {
        // Arrange
        $model = new UserAddress();
        $repository = new AddressRepository($model);

        // Act
        $repositoryModel = $repository->getModel();

        // Assert
        $this->assertSame($model, $repositoryModel);
    }

    #[Test]
    public function repository_follows_repository_pattern(): void
    {
        // Assert
        $this->assertInstanceOf(AddressRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(\App\Repositories\Base\BaseRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function repository_supports_address_operations(): void
    {
        // Assert - address specific methods
        $this->assertTrue(method_exists($this->repository, 'findByUser'));
        $this->assertTrue(method_exists($this->repository, 'unsetDefaultAddresses'));
    }

    #[Test]
    public function repository_has_proper_method_visibility(): void
    {
        // Assert - check public methods are accessible
        $reflection = new \ReflectionClass($this->repository);
        
        $findByUserMethod = $reflection->getMethod('findByUser');
        $unsetDefaultAddressesMethod = $reflection->getMethod('unsetDefaultAddresses');
        
        $this->assertTrue($findByUserMethod->isPublic());
        $this->assertTrue($unsetDefaultAddressesMethod->isPublic());
    }
}