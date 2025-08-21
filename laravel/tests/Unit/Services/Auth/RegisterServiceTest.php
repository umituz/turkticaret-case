<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\RegisterService;
use App\Repositories\User\UserRepositoryInterface;
use App\DTOs\Auth\RegisterDTO;
use App\Models\Auth\User;
use Tests\Base\BaseServiceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for RegisterService
 * Tests user registration logic with token generation
 */
#[CoversClass(RegisterService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class RegisterServiceTest extends BaseServiceUnitTest
{
    private UserRepositoryInterface $userRepositoryMock;

    protected function getServiceClass(): string
    {
        return RegisterService::class;
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
        $this->assertServiceHasMethod('register');
    }

    #[Test]
    public function register_creates_user_and_returns_user_with_token(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = $this->createMockUser([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $token = 'test-auth-token-12345';
        $tokenResult = $this->mockTokenResult($token);

        // Mock RegisterDTO
        $registerDTO = $this->mockDTO(RegisterDTO::class, $data);
        $registerDTO->shouldReceive('toArray')->andReturn($data);

        // Mock static method call
        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn($registerDTO);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($user);

        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($token, $result['token']);
    }

    #[Test]
    public function register_processes_dto_correctly(): void
    {
        // Arrange
        $inputData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'securepassword',
            'extra_field' => 'should_be_filtered'
        ];

        $processedData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'securepassword'
        ];

        $user = $this->createMockUser($processedData);
        $token = 'test-token-67890';
        $tokenResult = $this->mockTokenResult($token);

        // Mock RegisterDTO
        $registerDTO = $this->mockDTO(RegisterDTO::class, $processedData);
        $registerDTO->shouldReceive('toArray')->andReturn($processedData);

        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->with($inputData)
            ->andReturn($registerDTO);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($processedData)
            ->andReturn($user);

        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->register($inputData);

        // Assert
        $this->assertIsArray($result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($token, $result['token']);
    }

    #[Test]
    public function register_generates_auth_token_with_correct_name(): void
    {
        // Arrange
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'testpassword'
        ];

        $user = $this->createMockUser($data);
        $token = 'specific-auth-token';
        $tokenResult = $this->mockTokenResult($token);

        $registerDTO = $this->mockDTO(RegisterDTO::class, $data);
        $registerDTO->shouldReceive('toArray')->andReturn($data);

        RegisterDTO::shouldReceive('fromArray')
            ->with($data)
            ->andReturn($registerDTO);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->with($data)
            ->andReturn($user);

        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertEquals($token, $result['token']);
    }

    #[Test]
    public function register_returns_created_user_instance(): void
    {
        // Arrange
        $data = [
            'name' => 'Created User',
            'email' => 'created@example.com',
            'password' => 'createdpassword'
        ];

        $user = $this->createMockUser($data);
        $tokenResult = $this->mockTokenResult('token');

        $registerDTO = $this->mockDTO(RegisterDTO::class, $data);
        $registerDTO->shouldReceive('toArray')->andReturn($data);

        RegisterDTO::shouldReceive('fromArray')
            ->with($data)
            ->andReturn($registerDTO);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->with($data)
            ->andReturn($user);

        $user->shouldReceive('createToken')
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
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

    /**
     * Create mock token result
     */
    private function mockTokenResult(string $plainTextToken): \Mockery\MockInterface
    {
        $tokenResult = Mockery::mock();
        $tokenResult->plainTextToken = $plainTextToken;

        return $tokenResult;
    }
}