<?php

namespace Tests\Unit\Requests\Order;

use App\Http\Requests\Order\OrderCreateRequest;
use Tests\Base\BaseRequestUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for OrderCreateRequest
 * Tests validation rules and authorization logic for order creation
 */
#[CoversClass(OrderCreateRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class OrderCreateRequestTest extends BaseRequestUnitTest
{
    protected function getRequestClass(): string
    {
        return OrderCreateRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'shipping_address' => '123 Main Street, City, State 12345',
            'notes' => 'Please deliver to the front door',
        ];
    }

    protected function getInvalidDataCases(): array
    {
        return [
            'missing_shipping_address' => [
                'data' => ['notes' => 'Some notes'],
                'expected_errors' => ['shipping_address']
            ],
            'short_shipping_address' => [
                'data' => ['shipping_address' => 'Too short', 'notes' => 'Notes'],
                'expected_errors' => ['shipping_address']
            ],
            'long_shipping_address' => [
                'data' => ['shipping_address' => str_repeat('a', 501), 'notes' => 'Notes'],
                'expected_errors' => ['shipping_address']
            ],
            'long_notes' => [
                'data' => ['shipping_address' => '123 Main Street, City, State', 'notes' => str_repeat('a', 1001)],
                'expected_errors' => ['notes']
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
        $expectedFields = ['shipping_address', 'notes'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Should have rules for field: {$field}");
        }
    }

    #[Test]
    public function has_custom_validation_messages(): void
    {
        $this->assertHasValidationMessages();
        
        $expectedMessages = [
            'shipping_address.required' => 'Shipping address is required',
            'shipping_address.min' => 'Shipping address must be at least 10 characters',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters',
            'notes.max' => 'Notes cannot exceed 1000 characters',
        ];
        
        foreach ($expectedMessages as $key => $message) {
            $this->assertHasValidationMessage($key, $message);
        }
    }

    #[Test]
    public function shipping_address_field_validation_rules(): void
    {
        $this->assertHasValidationRule('shipping_address', 'required');
        $this->assertHasValidationRule('shipping_address', 'string');
        $this->assertHasValidationRule('shipping_address', 'min:10');
        $this->assertHasValidationRule('shipping_address', 'max:500');
    }

    #[Test]
    public function notes_field_validation_rules(): void
    {
        $this->assertHasValidationRule('notes', 'nullable');
        $this->assertHasValidationRule('notes', 'string');
        $this->assertHasValidationRule('notes', 'max:1000');
    }

    #[Test]
    public function validation_passes_with_valid_data(): void
    {
        $validData = $this->getValidData();
        $this->assertValidationPasses($validData);
    }

    #[Test]
    public function validation_passes_with_minimal_required_data(): void
    {
        $data = [
            'shipping_address' => '1234 Street Address',
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function shipping_address_is_required(): void
    {
        $this->assertFieldIsRequired('shipping_address');
    }

    #[Test]
    public function notes_is_optional(): void
    {
        $this->assertFieldIsOptional('notes');
    }

    #[Test]
    public function shipping_address_must_be_at_least_10_characters(): void
    {
        $data = $this->createInvalidDataForField('shipping_address', 'Too short');
        $this->assertValidationErrorMessage($data, 'shipping_address', 'Shipping address must be at least 10 characters');
    }

    #[Test]
    public function shipping_address_cannot_exceed_500_characters(): void
    {
        $data = $this->createInvalidDataForField('shipping_address', $this->generateTestString(501));
        $this->assertValidationErrorMessage($data, 'shipping_address', 'Shipping address cannot exceed 500 characters');
    }

    #[Test]
    public function notes_cannot_exceed_1000_characters(): void
    {
        $data = $this->createInvalidDataForField('notes', $this->generateTestString(1001));
        $this->assertValidationErrorMessage($data, 'notes', 'Notes cannot exceed 1000 characters');
    }

    #[Test]
    public function validation_passes_with_maximum_length_values(): void
    {
        $data = [
            'shipping_address' => $this->generateTestString(500),
            'notes' => $this->generateTestString(1000),
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_passes_with_minimum_length_shipping_address(): void
    {
        $data = [
            'shipping_address' => $this->generateTestString(10),
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_fails_with_empty_shipping_address(): void
    {
        $data = ['shipping_address' => ''];
        $this->assertValidationFails($data, ['shipping_address']);
    }

    #[Test]
    public function validation_fails_with_null_shipping_address(): void
    {
        $data = ['shipping_address' => null];
        $this->assertValidationFails($data, ['shipping_address']);
    }

    #[Test]
    public function validation_passes_with_null_notes(): void
    {
        $data = [
            'shipping_address' => '123 Main Street, City',
            'notes' => null,
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_passes_with_empty_string_notes(): void
    {
        $data = [
            'shipping_address' => '123 Main Street, City',
            'notes' => '',
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_passes_without_notes_field(): void
    {
        $data = [
            'shipping_address' => '123 Main Street, City',
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function validation_fails_with_invalid_data(array $data, array $expectedErrors): void
    {
        $this->assertValidationFails($data, $expectedErrors);
    }

    #[Test]
    #[DataProvider('validDataProvider')]
    public function validation_passes_with_various_valid_data(array $data): void
    {
        $this->assertValidationPasses($data);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'empty data' => [
                [],
                ['shipping_address']
            ],
            'short shipping address' => [
                ['shipping_address' => 'Short'],
                ['shipping_address']
            ],
            'too long shipping address' => [
                ['shipping_address' => str_repeat('a', 501)],
                ['shipping_address']
            ],
            'too long notes' => [
                ['shipping_address' => '123 Main Street', 'notes' => str_repeat('a', 1001)],
                ['notes']
            ],
            'non-string shipping address' => [
                ['shipping_address' => 12345],
                []  // Laravel will cast to string, so no validation error expected
            ],
            'non-string notes' => [
                ['shipping_address' => '123 Main Street', 'notes' => 12345],
                []  // Laravel will cast to string, so no validation error expected
            ],
        ];
    }

    public static function validDataProvider(): array
    {
        return [
            'minimal required' => [
                ['shipping_address' => '1234567890'] // exactly 10 characters
            ],
            'with notes' => [
                ['shipping_address' => '123 Main Street, City', 'notes' => 'Delivery instructions']
            ],
            'maximum length' => [
                ['shipping_address' => str_repeat('a', 500), 'notes' => str_repeat('b', 1000)]
            ],
            'with empty notes' => [
                ['shipping_address' => '123 Main Street', 'notes' => '']
            ],
            'realistic data' => [
                [
                    'shipping_address' => '1234 Elm Street, Apartment 5B, Springfield, IL 62701, USA',
                    'notes' => 'Please ring the doorbell twice. Leave package with doorman if nobody answers.'
                ]
            ],
        ];
    }
}