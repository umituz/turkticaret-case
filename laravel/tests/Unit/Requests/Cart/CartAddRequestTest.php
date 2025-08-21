<?php

namespace Tests\Unit\Requests\Cart;

use App\Http\Requests\Cart\CartAddRequest;
use Tests\Base\BaseRequestUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for CartAddRequest
 * Tests validation rules and authorization logic for adding items to cart
 */
#[CoversClass(CartAddRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class CartAddRequestTest extends BaseRequestUnitTest
{
    protected function getRequestClass(): string
    {
        return CartAddRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'product_uuid' => $this->generateTestUuid(),
            'quantity' => 5,
        ];
    }

    protected function getInvalidDataCases(): array
    {
        return [
            'missing_product_uuid' => [
                'data' => ['quantity' => 5],
                'expected_errors' => ['product_uuid']
            ],
            'missing_quantity' => [
                'data' => ['product_uuid' => $this->generateTestUuid()],
                'expected_errors' => ['quantity']
            ],
            'invalid_uuid' => [
                'data' => ['product_uuid' => 'invalid-uuid', 'quantity' => 5],
                'expected_errors' => ['product_uuid']
            ],
            'zero_quantity' => [
                'data' => ['product_uuid' => $this->generateTestUuid(), 'quantity' => 0],
                'expected_errors' => ['quantity']
            ],
            'negative_quantity' => [
                'data' => ['product_uuid' => $this->generateTestUuid(), 'quantity' => -1],
                'expected_errors' => ['quantity']
            ],
            'excessive_quantity' => [
                'data' => ['product_uuid' => $this->generateTestUuid(), 'quantity' => 101],
                'expected_errors' => ['quantity']
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
        $expectedFields = ['product_uuid', 'quantity'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Should have rules for field: {$field}");
        }
    }

    #[Test]
    public function has_custom_validation_messages(): void
    {
        $this->assertHasValidationMessages();
        
        $expectedMessages = [
            'product_uuid.required' => 'Product is required',
            'product_uuid.exists' => 'Selected product does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'quantity.max' => 'Quantity cannot exceed 100',
        ];
        
        foreach ($expectedMessages as $key => $message) {
            $this->assertHasValidationMessage($key, $message);
        }
    }

    #[Test]
    public function product_uuid_field_validation_rules(): void
    {
        $this->assertHasValidationRule('product_uuid', 'required');
        $this->assertHasValidationRule('product_uuid', 'uuid');
        $this->assertHasValidationRule('product_uuid', 'exists:products,uuid');
    }

    #[Test]
    public function quantity_field_validation_rules(): void
    {
        $this->assertHasValidationRule('quantity', 'required');
        $this->assertHasValidationRule('quantity', 'integer');
        $this->assertHasValidationRule('quantity', 'min:1');
        $this->assertHasValidationRule('quantity', 'max:100');
    }

    #[Test]
    public function validation_passes_with_valid_data(): void
    {
        $validData = $this->getValidData();
        $this->assertValidationPasses($validData);
    }

    #[Test]
    public function product_uuid_is_required(): void
    {
        $this->assertFieldIsRequired('product_uuid');
    }

    #[Test]
    public function quantity_is_required(): void
    {
        $this->assertFieldIsRequired('quantity');
    }

    #[Test]
    public function product_uuid_must_be_valid_uuid(): void
    {
        $data = $this->createInvalidDataForField('product_uuid', 'invalid-uuid');
        $this->assertValidationFails($data, ['product_uuid']);
    }

    #[Test]
    public function quantity_must_be_at_least_1(): void
    {
        $data = $this->createInvalidDataForField('quantity', 0);
        $this->assertValidationErrorMessage($data, 'quantity', 'Quantity must be at least 1');
    }

    #[Test]
    public function quantity_cannot_exceed_100(): void
    {
        $data = $this->createInvalidDataForField('quantity', 101);
        $this->assertValidationErrorMessage($data, 'quantity', 'Quantity cannot exceed 100');
    }

    #[Test]
    public function quantity_cannot_be_negative(): void
    {
        $data = $this->createInvalidDataForField('quantity', -1);
        $this->assertValidationFails($data, ['quantity']);
    }

    #[Test]
    public function validation_passes_with_minimum_quantity(): void
    {
        $data = array_merge($this->getValidData(), ['quantity' => 1]);
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_passes_with_maximum_quantity(): void
    {
        $data = array_merge($this->getValidData(), ['quantity' => 100]);
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function validation_fails_with_non_integer_quantity(): void
    {
        $data = $this->createInvalidDataForField('quantity', 'not-a-number');
        $this->assertValidationFails($data, ['quantity']);
    }

    #[Test]
    public function validation_fails_with_decimal_quantity(): void
    {
        $data = $this->createInvalidDataForField('quantity', 5.5);
        $this->assertValidationFails($data, ['quantity']);
    }

    #[Test]
    public function validation_fails_when_both_fields_missing(): void
    {
        $this->assertValidationFails([], ['product_uuid', 'quantity']);
    }

    #[Test]
    public function validation_fails_with_empty_values(): void
    {
        $data = [
            'product_uuid' => '',
            'quantity' => '',
        ];
        $this->assertValidationFails($data, ['product_uuid', 'quantity']);
    }

    #[Test]
    public function validation_fails_with_null_values(): void
    {
        $data = [
            'product_uuid' => null,
            'quantity' => null,
        ];
        $this->assertValidationFails($data, ['product_uuid', 'quantity']);
    }

    #[Test]
    #[DataProvider('validQuantityProvider')]
    public function validation_passes_with_valid_quantities($quantity): void
    {
        $data = array_merge($this->getValidData(), ['quantity' => $quantity]);
        $this->assertValidationPasses($data);
    }

    #[Test]
    #[DataProvider('invalidQuantityProvider')]
    public function validation_fails_with_invalid_quantities($quantity): void
    {
        $data = array_merge($this->getValidData(), ['quantity' => $quantity]);
        $this->assertValidationFails($data, ['quantity']);
    }

    #[Test]
    #[DataProvider('invalidUuidProvider')]
    public function validation_fails_with_invalid_uuids($uuid): void
    {
        $data = array_merge($this->getValidData(), ['product_uuid' => $uuid]);
        $this->assertValidationFails($data, ['product_uuid']);
    }

    public static function validQuantityProvider(): array
    {
        return [
            'minimum quantity' => [1],
            'middle quantity' => [50],
            'maximum quantity' => [100],
            'typical quantity' => [5],
            'string number' => ['10'], // Laravel will cast this
        ];
    }

    public static function invalidQuantityProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'too large' => [101],
            'way too large' => [1000],
            'decimal' => [5.5],
            'string' => ['not-a-number'],
            'null' => [null],
            'empty string' => [''],
        ];
    }

    public static function invalidUuidProvider(): array
    {
        return [
            'invalid format' => ['invalid-uuid'],
            'empty string' => [''],
            'null' => [null],
            'number' => [12345],
            'too short' => ['123-456'],
            'too long' => ['12345678-1234-1234-1234-123456789012-extra'],
            'wrong format' => ['12345678-1234-1234-1234'],
        ];
    }
}