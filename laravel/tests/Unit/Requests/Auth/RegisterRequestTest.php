<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use Tests\Base\BaseRequestUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for RegisterRequest
 * Tests validation rules and authorization logic for user registration
 */
#[CoversClass(RegisterRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class RegisterRequestTest extends BaseRequestUnitTest
{
    protected function getRequestClass(): string
    {
        return RegisterRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    protected function getInvalidDataCases(): array
    {
        return [
            'missing_name' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password123'],
                'expected_errors' => ['name']
            ],
            'missing_email' => [
                'data' => ['name' => 'John Doe', 'password' => 'password123'],
                'expected_errors' => ['email']
            ],
            'missing_password' => [
                'data' => ['name' => 'John Doe', 'email' => 'test@example.com'],
                'expected_errors' => ['password']
            ],
            'short_name' => [
                'data' => ['name' => 'J', 'email' => 'test@example.com', 'password' => 'password123'],
                'expected_errors' => ['name']
            ],
            'invalid_email' => [
                'data' => ['name' => 'John Doe', 'email' => 'invalid-email', 'password' => 'password123'],
                'expected_errors' => ['email']
            ],
            'short_password' => [
                'data' => ['name' => 'John Doe', 'email' => 'test@example.com', 'password' => '123'],
                'expected_errors' => ['password']
            ],
        ];
    }

    #[Test]
    public function request_is_form_request(): void
    {
        $this->assertIsFormRequest();
    }

    #[Test]
    public function request_has_required_methods(): void
    {
        $this->assertRequestHasMethod('authorize');
        $this->assertRequestHasMethod('rules');
        $this->assertRequestHasMethod('messages');
    }

    #[Test]
    public function authorize_returns_true(): void
    {
        $this->assertAuthorizationResult(true);
    }

    #[Test]
    public function has_validation_rules(): void
    {
        $this->assertHasValidationRules();
        
        $rules = $this->request->rules();
        $expectedFields = ['name', 'email', 'password'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Should have rules for field: {$field}");
        }
    }

    #[Test]
    public function has_custom_validation_messages(): void
    {
        $this->assertHasValidationMessages();
        
        $expectedMessages = [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters long.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];
        
        foreach ($expectedMessages as $key => $message) {
            $this->assertHasValidationMessage($key, $message);
        }
    }

    #[Test]
    public function name_field_validation_rules(): void
    {
        $this->assertHasValidationRule('name', 'required');
        $this->assertHasValidationRule('name', 'string');
        $this->assertHasValidationRule('name', 'min:2');
        $this->assertHasValidationRule('name', 'max:255');
    }

    #[Test]
    public function email_field_validation_rules(): void
    {
        $this->assertHasValidationRule('email', 'required');
        $this->assertHasValidationRule('email', 'string');
        $this->assertHasValidationRule('email', 'email');
        $this->assertHasValidationRule('email', 'max:255');
        $this->assertHasValidationRule('email', 'unique:users,email');
    }

    #[Test]
    public function password_field_validation_rules(): void
    {
        $this->assertHasValidationRule('password', 'required');
        $this->assertHasValidationRule('password', 'string');
        $this->assertHasValidationRule('password', 'min:8');
    }

    #[Test]
    public function validation_passes_with_valid_data(): void
    {
        $validData = $this->getValidData();
        $this->assertValidationPasses($validData);
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function validation_fails_with_invalid_data(array $data, array $expectedErrors): void
    {
        $this->assertValidationFails($data, $expectedErrors);
    }

    #[Test]
    public function name_is_required(): void
    {
        $this->assertFieldIsRequired('name');
    }

    #[Test]
    public function email_is_required(): void
    {
        $this->assertFieldIsRequired('email');
    }

    #[Test]
    public function password_is_required(): void
    {
        $this->assertFieldIsRequired('password');
    }

    #[Test]
    public function name_must_be_at_least_2_characters(): void
    {
        $data = $this->createInvalidDataForField('name', 'J');
        $this->assertValidationErrorMessage($data, 'name', 'Name must be at least 2 characters long.');
    }

    #[Test]
    public function name_cannot_exceed_255_characters(): void
    {
        $data = $this->createInvalidDataForField('name', $this->generateTestString(256));
        $this->assertValidationFails($data, ['name']);
    }

    #[Test]
    public function email_must_be_valid_format(): void
    {
        $data = $this->createInvalidDataForField('email', 'invalid-email');
        $this->assertValidationErrorMessage($data, 'email', 'Please enter a valid email address.');
    }

    #[Test]
    public function email_cannot_exceed_255_characters(): void
    {
        $longEmail = $this->generateTestString(250) . '@example.com'; // > 255 chars
        $data = $this->createInvalidDataForField('email', $longEmail);
        $this->assertValidationFails($data, ['email']);
    }

    #[Test]
    public function password_must_be_at_least_8_characters(): void
    {
        $data = $this->createInvalidDataForField('password', '1234567');
        $this->assertValidationErrorMessage($data, 'password', 'Password must be at least 8 characters long.');
    }

    #[Test]
    public function validation_passes_with_minimal_valid_data(): void
    {
        $data = [
            'name' => 'Jo',
            'email' => 'jo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_passes_with_maximum_length_data(): void
    {
        $password = $this->generateTestString(50);
        $data = [
            'name' => $this->generateTestString(255),
            'email' => $this->generateTestString(240) . '@test.com', // 250 chars total
            'password' => $password,
            'password_confirmation' => $password,
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_fails_when_all_fields_missing(): void
    {
        $this->assertValidationFails([], ['name', 'email', 'password']);
    }

    #[Test]
    public function validation_fails_with_empty_strings(): void
    {
        $data = [
            'name' => '',
            'email' => '',
            'password' => '',
        ];
        $this->assertValidationFails($data, ['name', 'email', 'password']);
    }

    #[Test]
    public function validation_fails_with_null_values(): void
    {
        $data = [
            'name' => null,
            'email' => null,
            'password' => null,
        ];
        $this->assertValidationFails($data, ['name', 'email', 'password']);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'missing name' => [
                ['email' => 'test@example.com', 'password' => 'password123'],
                ['name']
            ],
            'missing email' => [
                ['name' => 'John Doe', 'password' => 'password123'],
                ['email']
            ],
            'missing password' => [
                ['name' => 'John Doe', 'email' => 'test@example.com'],
                ['password']
            ],
            'invalid email format' => [
                ['name' => 'John Doe', 'email' => 'invalid-email', 'password' => 'password123'],
                ['email']
            ],
            'short password' => [
                ['name' => 'John Doe', 'email' => 'test@example.com', 'password' => '123'],
                ['password']
            ],
            'short name' => [
                ['name' => 'J', 'email' => 'test@example.com', 'password' => 'password123'],
                ['name']
            ],
        ];
    }
}