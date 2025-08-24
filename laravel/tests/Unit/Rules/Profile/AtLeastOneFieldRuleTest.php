<?php

namespace Tests\Unit\Rules\Profile;

use App\Rules\Profile\AtLeastOneFieldRule;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Mockery;

/**
 * Unit tests for AtLeastOneFieldRule validation rule
 * Tests validation logic for requiring at least one field
 */
#[CoversClass(AtLeastOneFieldRule::class)]
#[Group('unit')]
#[Group('rules')]
#[Small]
class AtLeastOneFieldRuleTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset any global request mock
        if (function_exists('app') && app()->has('request')) {
            app()->forgetInstance('request');
        }
    }

    #[Test]
    public function validate_passes_when_at_least_one_field_is_filled(): void
    {
        // Arrange
        $fields = ['first_name', 'last_name', 'email'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('first_name')
            ->andReturn(true);
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_fails_when_no_fields_are_filled(): void
    {
        // Arrange
        $fields = ['first_name', 'last_name', 'email'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('first_name')
            ->andReturn(false);
        $mockRequest->shouldReceive('filled')
            ->with('last_name')
            ->andReturn(false);
        $mockRequest->shouldReceive('filled')
            ->with('email')
            ->andReturn(false);
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $failMessage = '';
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertEquals('At least one field must be provided for update.', $failMessage);
    }

    #[Test]
    public function validate_passes_when_second_field_is_filled(): void
    {
        // Arrange
        $fields = ['first_name', 'last_name', 'phone'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('first_name')
            ->andReturn(false);
        $mockRequest->shouldReceive('filled')
            ->with('last_name')
            ->andReturn(true);
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_passes_when_last_field_is_filled(): void
    {
        // Arrange
        $fields = ['address', 'city', 'postal_code'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('address')
            ->andReturn(false);
        $mockRequest->shouldReceive('filled')
            ->with('city')
            ->andReturn(false);
        $mockRequest->shouldReceive('filled')
            ->with('postal_code')
            ->andReturn(true);
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_works_with_single_field(): void
    {
        // Arrange
        $fields = ['description'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('description')
            ->andReturn(true);
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function validate_fails_with_single_empty_field(): void
    {
        // Arrange
        $fields = ['bio'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('bio')
            ->andReturn(false);
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertTrue($failCalled);
    }

    #[Test]
    public function validate_passes_when_multiple_fields_are_filled(): void
    {
        // Arrange
        $fields = ['name', 'email', 'phone'];
        $rule = new AtLeastOneFieldRule($fields);
        
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('filled')
            ->with('name')
            ->andReturn(true);
        // Note: Should stop checking after first filled field
        
        $this->app->instance('request', $mockRequest);
        
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate('test_attribute', 'test_value', $fail);

        // Assert
        $this->assertFalse($failCalled);
    }

    #[Test]
    public function constructor_accepts_array_of_fields(): void
    {
        // Arrange & Act
        $fields = ['field1', 'field2', 'field3'];
        $rule = new AtLeastOneFieldRule($fields);

        // Assert - Test that the rule was created successfully
        $this->assertInstanceOf(AtLeastOneFieldRule::class, $rule);
    }

    #[Test]
    public function rule_implements_validation_rule_interface(): void
    {
        // Arrange & Act
        $rule = new AtLeastOneFieldRule(['test']);

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Validation\ValidationRule::class, $rule);
    }
}