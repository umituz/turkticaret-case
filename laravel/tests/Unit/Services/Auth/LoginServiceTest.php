<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\LoginService;
use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;

/**
 * Unit tests for LoginService
 * Tests authentication login operations with proper mocking
 */
#[CoversClass(LoginService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class LoginServiceTest extends UnitTestCase
{
    private LoginService $loginService;
    private MockInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->loginService = new LoginService($this->userRepository);
    }

    #[Test]
    public function login_returns_user_and_token_with_valid_credentials(): void
    {
        // Arrange
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $mockUser = $this->mock(User::class);
        $mockToken = Mockery::mock();
        $expectedToken = 'mock-token-string';
        $hashedPassword = '$2y$10$test.hash.value';
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn($mockUser);
            
        // Mock password property access
        $mockUser->password = $hashedPassword;
        
        Hash::shouldReceive('check')
            ->once()
            ->with('password123', $hashedPassword)
            ->andReturn(true);
            
        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($mockToken);
            
        $mockToken->plainTextToken = $expectedToken;

        // Act
        $result = $this->loginService->login($credentials);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertSame($mockUser, $result['user']);
        $this->assertEquals($expectedToken, $result['token']);
    }

    #[Test]
    public function login_throws_validation_exception_when_user_not_found(): void
    {
        // Arrange
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('nonexistent@example.com')
            ->andReturn(null);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->loginService->login($credentials);
    }

    #[Test]
    public function login_throws_validation_exception_when_password_incorrect(): void
    {
        // Arrange
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];
        
        $mockUser = Mockery::mock(User::class);
        $hashedPassword = Hash::make('correctpassword');
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn($mockUser);
            
        $mockUser->shouldReceive('getAttribute')
            ->with('password')
            ->andReturn($hashedPassword);
            
        Hash::shouldReceive('check')
            ->once()
            ->with('wrongpassword', $hashedPassword)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->loginService->login($credentials);
    }

    #[Test]
    public function logout_returns_true_when_token_deleted_successfully(): void
    {
        // Arrange
        $mockUser = Mockery::mock(User::class);
        $mockToken = Mockery::mock();
        
        $mockUser->shouldReceive('currentAccessToken')
            ->once()
            ->andReturn($mockToken);
            
        $mockToken->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->loginService->logout($mockUser);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function logout_returns_false_when_token_deletion_fails(): void
    {
        // Arrange
        $mockUser = Mockery::mock(User::class);
        $mockToken = Mockery::mock();
        
        $mockUser->shouldReceive('currentAccessToken')
            ->once()
            ->andReturn($mockToken);
            
        $mockToken->shouldReceive('delete')
            ->once()
            ->andReturn(false);

        // Act
        $result = $this->loginService->logout($mockUser);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function constructor_accepts_user_repository(): void
    {
        // Act
        $service = new LoginService($this->userRepository);

        // Assert
        $this->assertInstanceOf(LoginService::class, $service);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->loginService, 'login'));
        $this->assertTrue(method_exists($this->loginService, 'logout'));
    }

    #[Test]
    public function login_validation_exception_has_correct_message(): void
    {
        // Arrange
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn(null);

        // Act & Assert
        try {
            $this->loginService->login($credentials);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('email', $errors);
            $this->assertContains('The provided credentials are incorrect.', $errors['email']);
        }
    }

    #[Test]
    public function login_uses_hash_facade_for_password_verification(): void
    {
        // Arrange
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'testpassword'
        ];
        
        $mockUser = Mockery::mock(User::class);
        $hashedPassword = 'hashed-password';
        
        $mockUser->shouldReceive('getAttribute')
            ->with('password')
            ->andReturn($hashedPassword);
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn($mockUser);

        Hash::shouldReceive('check')
            ->once()
            ->with('testpassword', $hashedPassword)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->loginService->login($credentials);
    }

    #[Test]
    public function login_creates_auth_token_with_correct_name(): void
    {
        // Arrange
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $mockUser = Mockery::mock(User::class);
        $mockToken = Mockery::mock();
        $mockToken->plainTextToken = 'test-token';
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn($mockUser);
            
        $hashedPassword = Hash::make('password123');
        
        $mockUser->shouldReceive('getAttribute')
            ->with('password')
            ->andReturn($hashedPassword);
        
        Hash::shouldReceive('check')
            ->once()
            ->with('password123', $hashedPassword)
            ->andReturn(true);
            
        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($mockToken);

        // Act
        $result = $this->loginService->login($credentials);

        // Assert
        $this->assertEquals('test-token', $result['token']);
    }
}