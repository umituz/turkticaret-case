<?php

namespace Tests\Unit\Services\Profile;

use App\Services\Profile\ProfileService;
use App\Repositories\User\UserRepositoryInterface;
use App\DTOs\Profile\ProfileUpdateDTO;
use App\Models\Auth\User;
use Tests\Base\BaseServiceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for ProfileService
 * Tests user profile retrieval and update functionality
 */
#[CoversClass(ProfileService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class ProfileServiceTest extends BaseServiceUnitTest
{
    private UserRepositoryInterface $userRepositoryMock;

    protected function getServiceClass(): string
    {
        return ProfileService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->userRepositoryMock = $this->mockRepository(UserRepositoryInterface::class);

        return [
            $this->userRepositoryMock
        ];
    }

    #[Test]
    public function service_has_required_constructor_dependencies(): void
    {
        $this->assertHasConstructorDependencies([
            UserRepositoryInterface::class
        ]);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        $this->assertServiceHasMethod('getProfile');
        $this->assertServiceHasMethod('updateProfile');
    }

    #[Test]
    public function getProfile_returns_user_instance(): void
    {
        // Arrange
        $user = $this->createMockUser();

        // Act
        $result = $this->service->getProfile($user);

        // Assert
        $this->assertServiceReturns($result, User::class);
        $this->assertSame($user, $result);
    }

    #[Test]
    public function updateProfile_updates_user_and_returns_updated_instance(): void
    {
        // Arrange
        $user = $this->createMockUser();
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $updatedUser = $this->createMockUser([
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        // Mock ProfileUpdateDTO
        $profileDTO = $this->mockDTO(ProfileUpdateDTO::class, $data);
        $profileDTO->shouldReceive('toArray')->andReturn($data);

        ProfileUpdateDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn($profileDTO);

        $this->userRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($user->uuid, $data)
            ->andReturn(true);

        $this->userRepositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($user->uuid)
            ->andReturn($updatedUser);

        // Act
        $result = $this->service->updateProfile($user, $data);

        // Assert
        $this->assertServiceReturns($result, User::class);
        $this->assertServiceUsesRepository($this->userRepositoryMock, 'updateByUuid', [$user->uuid, $data]);
        $this->assertServiceUsesRepository($this->userRepositoryMock, 'findByUuid', [$user->uuid]);
    }

    #[Test]
    public function updateProfile_processes_dto_correctly(): void
    {
        // Arrange
        $user = $this->createMockUser();
        $inputData = [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'extra_field' => 'should_be_filtered'
        ];

        $processedData = [
            'name' => 'New Name',
            'email' => 'new@example.com'
        ];

        $updatedUser = $this->createMockUser($processedData);

        // Mock ProfileUpdateDTO
        $profileDTO = $this->mockDTO(ProfileUpdateDTO::class, $processedData);
        $profileDTO->shouldReceive('toArray')->andReturn($processedData);

        ProfileUpdateDTO::shouldReceive('fromArray')
            ->once()
            ->with($inputData)
            ->andReturn($profileDTO);

        $this->userRepositoryMock
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($user->uuid, $processedData)
            ->andReturn(true);

        $this->userRepositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($user->uuid)
            ->andReturn($updatedUser);

        // Act
        $result = $this->service->updateProfile($user, $inputData);

        // Assert
        $this->assertServiceReturns($result, User::class);
    }

    #[Test]
    public function updateProfile_returns_fresh_user_instance(): void
    {
        // Arrange
        $originalUser = $this->createMockUser([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $data = [
            'name' => 'Fresh Name',
            'email' => 'fresh@example.com'
        ];

        $freshUser = $this->createMockUser($data);

        $profileDTO = $this->mockDTO(ProfileUpdateDTO::class, $data);
        $profileDTO->shouldReceive('toArray')->andReturn($data);

        ProfileUpdateDTO::shouldReceive('fromArray')
            ->with($data)
            ->andReturn($profileDTO);

        $this->userRepositoryMock
            ->shouldReceive('updateByUuid')
            ->with($originalUser->uuid, $data)
            ->andReturn(true);

        $this->userRepositoryMock
            ->shouldReceive('findByUuid')
            ->with($originalUser->uuid)
            ->andReturn($freshUser);

        // Act
        $result = $this->service->updateProfile($originalUser, $data);

        // Assert
        $this->assertSame($freshUser, $result);
        $this->assertEquals($data['name'], $freshUser->name);
        $this->assertEquals($data['email'], $freshUser->email);
    }

    #[Test]
    public function updateProfile_with_partial_data(): void
    {
        // Arrange
        $user = $this->createMockUser();
        $data = ['name' => 'Only Name Update'];
        $updatedUser = $this->createMockUser(['name' => 'Only Name Update']);

        $profileDTO = $this->mockDTO(ProfileUpdateDTO::class, $data);
        $profileDTO->shouldReceive('toArray')->andReturn($data);

        ProfileUpdateDTO::shouldReceive('fromArray')
            ->with($data)
            ->andReturn($profileDTO);

        $this->userRepositoryMock
            ->shouldReceive('updateByUuid')
            ->with($user->uuid, $data)
            ->andReturn(true);

        $this->userRepositoryMock
            ->shouldReceive('findByUuid')
            ->with($user->uuid)
            ->andReturn($updatedUser);

        // Act
        $result = $this->service->updateProfile($user, $data);

        // Assert
        $this->assertServiceReturns($result, User::class);
        $this->assertEquals($data['name'], $result->name);
    }

    #[Test]
    public function getProfile_with_different_users(): void
    {
        // Arrange
        $user1 = $this->createMockUser(['name' => 'User One', 'email' => 'user1@example.com']);
        $user2 = $this->createMockUser(['name' => 'User Two', 'email' => 'user2@example.com']);

        // Act
        $result1 = $this->service->getProfile($user1);
        $result2 = $this->service->getProfile($user2);

        // Assert
        $this->assertSame($user1, $result1);
        $this->assertSame($user2, $result2);
        $this->assertNotSame($result1, $result2);
    }

    /**
     * Create mock User
     */
    private function createMockUser(array $attributes = []): \Mockery\MockInterface
    {
        $defaultAttributes = [
            'uuid' => $this->getTestUserUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
        ];

        return $this->mockTypedModel(User::class, array_merge($defaultAttributes, $attributes));
    }
}