<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\LoginService;
use App\Repositories\User\UserRepositoryInterface;
use App\Models\Auth\User;
use Tests\Base\BaseServiceUnitTest;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for LoginService
 * Tests authentication logic with credential validation
 */
#[CoversClass(LoginService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class LoginServiceTest extends BaseServiceUnitTest
{
    private UserRepositoryInterface $userRepositoryMock;

    protected function getServiceClass(): string
    {
        return LoginService::class;
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
        $this->assertServiceHasMethod('login');
    }

    #[Test]
    public function login_authenticates_user_and_returns_user_with_token(): void
    {
        // Arrange
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $user = $this->createMockUser([
            'email' => 'john@example.com',
            'password' => $hashedPassword,
            'email_verified_at' => now()
        ]);

        $token = 'test-login-token-12345';
        $tokenResult = $this->mockTokenResult($token);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn($user);

        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->login($credentials);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($token, $result['token']);
    }

    #[Test]
    public function login_throws_exception_when_user_not_found(): void
    {
        // Arrange
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn(null);

        // Act & Assert
        $this->assertServiceThrowsException(
            fn() => $this->service->login($credentials),
            ValidationException::class
        );
    }

    #[Test]
    public function login_throws_exception_when_password_incorrect(): void
    {
        // Arrange
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $hashedPassword = password_hash('correctpassword', PASSWORD_DEFAULT);
        $user = $this->createMockUser([
            'email' => 'john@example.com',
            'password' => $hashedPassword,
            'email_verified_at' => now()
        ]);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn($user);

        // Act & Assert
        $this->assertServiceThrowsException(
            fn() => $this->service->login($credentials),
            ValidationException::class
        );
    }

    #[Test]
    public function login_generates_auth_token_with_correct_name(): void
    {
        // Arrange
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'testpassword'
        ];

        $hashedPassword = password_hash('testpassword', PASSWORD_DEFAULT);
        $user = $this->createMockUser([
            'email' => 'test@example.com',
            'password' => $hashedPassword
        ]);

        $token = 'specific-login-token';
        $tokenResult = $this->mockTokenResult($token);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with($credentials['email'])
            ->andReturn($user);

        $this->app['hash']->shouldReceive('check')
            ->with($credentials['password'], $hashedPassword)
            ->andReturn(true);

        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->login($credentials);

        // Assert
        $this->assertEquals($token, $result['token']);
    }

    #[Test]
    public function login_returns_found_user_instance(): void
    {
        // Arrange
        $credentials = [
            'email' => 'found@example.com',
            'password' => 'foundpassword'
        ];

        $hashedPassword = password_hash('foundpassword', PASSWORD_DEFAULT);
        $user = $this->createMockUser([
            'email' => 'found@example.com',
            'password' => $hashedPassword
        ]);

        $tokenResult = $this->mockTokenResult('token');

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with($credentials['email'])
            ->andReturn($user);

        $this->app['hash']->shouldReceive('check')
            ->with($credentials['password'], $hashedPassword)
            ->andReturn(true);

        $user->shouldReceive('createToken')
            ->with('auth-token')
            ->andReturn($tokenResult);

        // Act
        $result = $this->service->login($credentials);

        // Assert
        $this->assertSame($user, $result['user']);
        $this->assertEquals($credentials['email'], $user->email);
    }

    #[Test]
    public function findAndVerifyUser_returns_user_when_credentials_valid(): void
    {
        // Arrange
        $email = 'valid@example.com';
        $password = 'validpassword';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $user = $this->createMockUser([
            'email' => $email,
            'password' => $hashedPassword
        ]);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with($email)
            ->andReturn($user);

        $this->app['hash']->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        // Act - Using reflection to call protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('findAndVerifyUser');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $email, $password);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user, $result);
    }

    #[Test]
    public function findAndVerifyUser_throws_exception_when_user_not_found(): void
    {
        // Arrange
        $email = 'notfound@example.com';
        $password = 'password';

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with($email)
            ->andReturn(null);

        // Act & Assert
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('findAndVerifyUser');
        $method->setAccessible(true);

        $this->expectException(ValidationException::class);
        $method->invoke($this->service, $email, $password);
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
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'email_verified_at' => now(),
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