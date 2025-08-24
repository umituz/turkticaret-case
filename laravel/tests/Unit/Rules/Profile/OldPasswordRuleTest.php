<?php

namespace Tests\Unit\Rules\Profile;

use App\Rules\Profile\OldPasswordRule;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User\User;
use Mockery;

/**
 * Unit tests for OldPasswordRule validation rule
 * Tests password verification logic
 */
#[CoversClass(OldPasswordRule::class)]
#[Group('unit')]
#[Group('rules')]
#[Small]
class OldPasswordRuleTest extends UnitTestCase
{
    #[Test]
    public function validate_passes_when_old_password_matches(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->password = 'hashed_password';
        
        $rule = new OldPasswordRule($user);
        
        Hash::shouldReceive('check')
            ->once()
            ->with('old_password', 'hashed_password')
            ->andReturn(true);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('old_password', 'old_password', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_fails_when_old_password_is_incorrect(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->password = 'hashed_password';
        
        $rule = new OldPasswordRule($user);
        
        Hash::shouldReceive('check')
            ->once()
            ->with('wrong_password', 'hashed_password')
            ->andReturn(false);
        
        $failCalled = false;
        $failMessage = '';
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate('old_password', 'wrong_password', $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertEquals('The old password is incorrect.', $failMessage);
    }

    #[Test]
    public function validate_fails_when_user_is_null(): void
    {
        // Arrange
        $rule = new OldPasswordRule(null);
        
        $failCalled = false;
        $failMessage = '';
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate('old_password', 'some_password', $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertEquals('Authentication required.', $failMessage);
    }

    #[Test]
    public function constructor_uses_auth_user_when_no_user_provided(): void
    {
        // Arrange
        $authUser = Mockery::mock(User::class);
        $authUser->password = 'auth_user_hashed_password';
        
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($authUser);
        
        Hash::shouldReceive('check')
            ->once()
            ->with('test_password', 'auth_user_hashed_password')
            ->andReturn(true);
        
        $rule = new OldPasswordRule();
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('old_password', 'test_password', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function constructor_uses_provided_user_over_auth_user(): void
    {
        // Arrange
        $providedUser = Mockery::mock(User::class);
        $providedUser->password = 'provided_user_password';
        
        // Auth::user() should not be called since we provide a user
        Auth::shouldNotReceive('user');
        
        Hash::shouldReceive('check')
            ->once()
            ->with('test_password', 'provided_user_password')
            ->andReturn(true);
        
        $rule = new OldPasswordRule($providedUser);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('old_password', 'test_password', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_handles_null_auth_user_gracefully(): void
    {
        // Arrange
        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);
        
        $rule = new OldPasswordRule();
        
        $failCalled = false;
        $failMessage = '';
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate('old_password', 'some_password', $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertEquals('Authentication required.', $failMessage);
    }

    #[Test]
    public function validate_works_with_different_password_values(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->password = 'complex_hashed_password_123';
        
        $rule = new OldPasswordRule($user);
        
        Hash::shouldReceive('check')
            ->once()
            ->with('complex_password_123', 'complex_hashed_password_123')
            ->andReturn(true);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('old_password', 'complex_password_123', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_fails_with_empty_password(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->password = 'hashed_password';
        
        $rule = new OldPasswordRule($user);
        
        Hash::shouldReceive('check')
            ->once()
            ->with('', 'hashed_password')
            ->andReturn(false);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('old_password', '', $fail);

        // Assert
        $this->assertTrue($failCalled);
    }

    #[Test]
    public function rule_implements_validation_rule_interface(): void
    {
        // Arrange & Act
        $rule = new OldPasswordRule();

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Validation\ValidationRule::class, $rule);
    }

    #[Test]
    public function constructor_accepts_user_parameter(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        
        // Act
        $rule = new OldPasswordRule($user);

        // Assert - Test that the rule was created successfully
        $this->assertInstanceOf(OldPasswordRule::class, $rule);
    }
}