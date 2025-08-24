<?php

namespace Tests\Unit\Services\User\Address;

use App\Services\User\Address\AddressService;
use App\DTOs\User\Address\AddressDTO;
use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\User\Address\AddressRepositoryInterface;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Database\Eloquent\Collection;
use Mockery\MockInterface;

/**
 * Unit tests for AddressService
 * Tests user address operations with proper mocking
 */
#[CoversClass(AddressService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class AddressServiceTest extends UnitTestCase
{
    private AddressService $addressService;
    private MockInterface $addressRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->addressRepository = $this->mock(AddressRepositoryInterface::class);
        $this->addressService = new AddressService($this->addressRepository);
    }

    #[Test]
    public function constructor_accepts_address_repository(): void
    {
        // Act
        $service = new AddressService($this->addressRepository);

        // Assert
        $this->assertInstanceOf(AddressService::class, $service);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->addressService, 'getUserAddresses'));
        $this->assertTrue(method_exists($this->addressService, 'createAddress'));
        $this->assertTrue(method_exists($this->addressService, 'updateAddressByModel'));
        $this->assertTrue(method_exists($this->addressService, 'deleteAddressByModel'));
    }

    #[Test]
    public function get_user_addresses_returns_collection(): void
    {
        // Arrange
        $user = $this->mock(User::class);
        $expectedCollection = new Collection([
            $this->mock(UserAddress::class),
            $this->mock(UserAddress::class)
        ]);

        $this->addressRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($user)
            ->andReturn($expectedCollection);

        // Act
        $result = $this->addressService->getUserAddresses($user);

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function create_address_creates_address_with_user_uuid(): void
    {
        // Arrange
        $user = $this->mock(User::class);
        $userUuid = $this->generateTestUuid();
        $user->uuid = $userUuid;
        
        $addressData = [
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'Anytown',
            'postal_code' => '12345',
            'is_default' => false
        ];

        $expectedAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(function ($data) use ($userUuid) {
                return isset($data['user_uuid']) && $data['user_uuid'] === $userUuid;
            }))
            ->andReturn($expectedAddress);

        // Act
        $result = $this->addressService->createAddress($user, $addressData);

        // Assert
        $this->assertSame($expectedAddress, $result);
    }

    #[Test]
    public function create_address_unsets_default_when_new_default_address(): void
    {
        // Arrange
        $user = $this->mock(User::class);
        $userUuid = $this->generateTestUuid();
        $user->uuid = $userUuid;
        
        $addressData = [
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'Anytown',
            'postal_code' => '12345',
            'is_default' => true  // This should trigger unset of other defaults
        ];

        $expectedAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('unsetDefaultAddresses')
            ->once()
            ->with($user);

        $this->addressRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedAddress);

        // Act
        $result = $this->addressService->createAddress($user, $addressData);

        // Assert
        $this->assertSame($expectedAddress, $result);
    }

    #[Test]
    public function create_address_does_not_unset_default_when_not_default(): void
    {
        // Arrange
        $user = $this->mock(User::class);
        $userUuid = $this->generateTestUuid();
        $user->uuid = $userUuid;
        
        $addressData = [
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'Anytown',
            'postal_code' => '12345',
            'is_default' => false
        ];

        $expectedAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldNotReceive('unsetDefaultAddresses');

        $this->addressRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedAddress);

        // Act
        $result = $this->addressService->createAddress($user, $addressData);

        // Assert
        $this->assertSame($expectedAddress, $result);
    }

    #[Test]
    public function update_address_by_model_returns_fresh_address(): void
    {
        // Arrange
        $address = $this->mock(UserAddress::class);
        $user = $this->mock(User::class);
        $address->user = $user;
        
        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'is_default' => false
        ];

        $freshAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('update')
            ->once()
            ->with($address, \Mockery::type('array'));

        $address->shouldReceive('fresh')
            ->once()
            ->andReturn($freshAddress);

        // Act
        $result = $this->addressService->updateAddressByModel($address, $updateData);

        // Assert
        $this->assertSame($freshAddress, $result);
    }

    #[Test]
    public function update_address_unsets_default_when_updating_to_default(): void
    {
        // Arrange
        $address = $this->mock(UserAddress::class);
        $user = $this->mock(User::class);
        $address->user = $user;
        
        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'is_default' => true  // This should trigger unset of other defaults
        ];

        $freshAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('unsetDefaultAddresses')
            ->once()
            ->with($user);

        $this->addressRepository
            ->shouldReceive('update')
            ->once()
            ->with($address, \Mockery::type('array'));

        $address->shouldReceive('fresh')
            ->once()
            ->andReturn($freshAddress);

        // Act
        $result = $this->addressService->updateAddressByModel($address, $updateData);

        // Assert
        $this->assertSame($freshAddress, $result);
    }

    #[Test]
    public function update_address_does_not_unset_default_when_not_updating_to_default(): void
    {
        // Arrange
        $address = $this->mock(UserAddress::class);
        $user = $this->mock(User::class);
        $address->user = $user;
        
        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'is_default' => false
        ];

        $freshAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldNotReceive('unsetDefaultAddresses');

        $this->addressRepository
            ->shouldReceive('update')
            ->once()
            ->with($address, \Mockery::type('array'));

        $address->shouldReceive('fresh')
            ->once()
            ->andReturn($freshAddress);

        // Act
        $result = $this->addressService->updateAddressByModel($address, $updateData);

        // Assert
        $this->assertSame($freshAddress, $result);
    }

    #[Test]
    public function delete_address_by_model_returns_true_on_success(): void
    {
        // Arrange
        $address = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('delete')
            ->once()
            ->with($address)
            ->andReturn(true);

        // Act
        $result = $this->addressService->deleteAddressByModel($address);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function delete_address_by_model_returns_false_on_failure(): void
    {
        // Arrange
        $address = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('delete')
            ->once()
            ->with($address)
            ->andReturn(false);

        // Act
        $result = $this->addressService->deleteAddressByModel($address);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function service_uses_address_dto_for_create(): void
    {
        // Arrange
        $user = $this->mock(User::class);
        $userUuid = $this->generateTestUuid();
        $user->uuid = $userUuid;
        
        $addressData = [
            'type' => 'billing',
            'first_name' => 'Test',
            'last_name' => 'User'
        ];

        $expectedAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(function ($data) use ($userUuid) {
                return is_array($data) && 
                       isset($data['user_uuid']) && 
                       $data['user_uuid'] === $userUuid &&
                       isset($data['type']) &&
                       $data['type'] === 'billing';
            }))
            ->andReturn($expectedAddress);

        // Act
        $result = $this->addressService->createAddress($user, $addressData);

        // Assert
        $this->assertSame($expectedAddress, $result);
    }

    #[Test]
    public function service_uses_address_dto_for_update(): void
    {
        // Arrange
        $address = $this->mock(UserAddress::class);
        $user = $this->mock(User::class);
        $address->user = $user;
        
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name'
        ];

        $freshAddress = $this->mock(UserAddress::class);

        $this->addressRepository
            ->shouldReceive('update')
            ->once()
            ->with($address, \Mockery::on(function ($data) {
                return is_array($data) && 
                       isset($data['first_name']) && 
                       $data['first_name'] === 'Updated';
            }));

        $address->shouldReceive('fresh')
            ->once()
            ->andReturn($freshAddress);

        // Act
        $result = $this->addressService->updateAddressByModel($address, $updateData);

        // Assert
        $this->assertSame($freshAddress, $result);
    }
}