<?php

namespace Tests\Unit\Requests\Product;

use App\Http\Requests\Product\ProductCreateRequest;
use Tests\Base\BaseRequestUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for ProductCreateRequest
 * Tests validation rules and authorization logic for product creation
 */
#[CoversClass(ProductCreateRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class ProductCreateRequestTest extends BaseRequestUnitTest
{
    protected function getRequestClass(): string
    {
        return ProductCreateRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'name' => 'Test Product',
            'description' => 'Test product description',
            'sku' => 'TEST-SKU-001',
            'price' => 1000,
            'stock_quantity' => 50,
            'image_path' => '/images/test-product.jpg',
            'is_active' => true,
            'category_uuid' => $this->generateTestUuid(),
        ];
    }

    protected function getInvalidDataCases(): array
    {
        return [
            'missing_required_fields' => [
                'data' => [],
                'expected_errors' => ['name', 'sku', 'price', 'stock_quantity', 'category_uuid']
            ],
            'short_name' => [
                'data' => array_merge($this->getValidData(), ['name' => 'Te']),
                'expected_errors' => ['name']
            ],
            'invalid_price' => [
                'data' => array_merge($this->getValidData(), ['price' => 0]),
                'expected_errors' => ['price']
            ],
            'negative_stock' => [
                'data' => array_merge($this->getValidData(), ['stock_quantity' => -1]),
                'expected_errors' => ['stock_quantity']
            ],
            'invalid_uuid' => [
                'data' => array_merge($this->getValidData(), ['category_uuid' => 'invalid-uuid']),
                'expected_errors' => ['category_uuid']
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
        $expectedFields = ['name', 'sku', 'price', 'stock_quantity', 'category_uuid'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Should have rules for field: {$field}");
        }
    }

    #[Test]
    public function has_custom_validation_messages(): void
    {
        $this->assertHasValidationMessages();
        
        $expectedMessages = [
            'name.required' => 'Product name is required',
            'name.min' => 'Product name must be at least 3 characters',
            'sku.required' => 'SKU is required',
            'sku.unique' => 'SKU already exists',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be at least 1',
            'stock_quantity.required' => 'Stock quantity is required',
            'stock_quantity.min' => 'Stock quantity cannot be negative',
            'category_uuid.required' => 'Category is required',
            'category_uuid.exists' => 'Selected category does not exist',
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
        $this->assertHasValidationRule('name', 'min:3');
        $this->assertHasValidationRule('name', 'max:255');
    }

    #[Test]
    public function description_field_validation_rules(): void
    {
        $this->assertHasValidationRule('description', 'nullable');
        $this->assertHasValidationRule('description', 'string');
        $this->assertHasValidationRule('description', 'max:1000');
    }

    #[Test]
    public function sku_field_validation_rules(): void
    {
        $this->assertHasValidationRule('sku', 'required');
        $this->assertHasValidationRule('sku', 'string');
        $this->assertHasValidationRule('sku', 'max:50');
        $this->assertHasValidationRule('sku', 'unique:products,sku');
    }

    #[Test]
    public function price_field_validation_rules(): void
    {
        $this->assertHasValidationRule('price', 'required');
        $this->assertHasValidationRule('price', 'integer');
        $this->assertHasValidationRule('price', 'min:1');
    }

    #[Test]
    public function stock_quantity_field_validation_rules(): void
    {
        $this->assertHasValidationRule('stock_quantity', 'required');
        $this->assertHasValidationRule('stock_quantity', 'integer');
        $this->assertHasValidationRule('stock_quantity', 'min:0');
    }

    #[Test]
    public function category_uuid_field_validation_rules(): void
    {
        $this->assertHasValidationRule('category_uuid', 'required');
        $this->assertHasValidationRule('category_uuid', 'uuid');
        $this->assertHasValidationRule('category_uuid', 'exists:categories,uuid');
    }

    #[Test]
    public function image_path_field_validation_rules(): void
    {
        $this->assertHasValidationRule('image_path', 'nullable');
        $this->assertHasValidationRule('image_path', 'string');
        $this->assertHasValidationRule('image_path', 'max:500');
    }

    #[Test]
    public function is_active_field_validation_rules(): void
    {
        $this->assertHasValidationRule('is_active', 'boolean');
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
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 100,
            'stock_quantity' => 10,
            'category_uuid' => $this->generateTestUuid(),
        ];
        $this->assertValidationPasses($data);
    }

    #[Test]
    public function name_is_required(): void
    {
        $this->assertFieldIsRequired('name');
    }

    #[Test]
    public function sku_is_required(): void
    {
        $this->assertFieldIsRequired('sku');
    }

    #[Test]
    public function price_is_required(): void
    {
        $this->assertFieldIsRequired('price');
    }

    #[Test]
    public function stock_quantity_is_required(): void
    {
        $this->assertFieldIsRequired('stock_quantity');
    }

    #[Test]
    public function category_uuid_is_required(): void
    {
        $this->assertFieldIsRequired('category_uuid');
    }

    #[Test]
    public function description_is_optional(): void
    {
        $this->assertFieldIsOptional('description');
    }

    #[Test]
    public function image_path_is_optional(): void
    {
        $this->assertFieldIsOptional('image_path');
    }

    #[Test]
    public function is_active_is_optional(): void
    {
        $this->assertFieldIsOptional('is_active');
    }

    #[Test]
    public function name_must_be_at_least_3_characters(): void
    {
        $data = $this->createInvalidDataForField('name', 'Te');
        $this->assertValidationErrorMessage($data, 'name', 'Product name must be at least 3 characters');
    }

    #[Test]
    public function price_must_be_at_least_1(): void
    {
        $data = $this->createInvalidDataForField('price', 0);
        $this->assertValidationErrorMessage($data, 'price', 'Price must be at least 1');
    }

    #[Test]
    public function stock_quantity_cannot_be_negative(): void
    {
        $data = $this->createInvalidDataForField('stock_quantity', -1);
        $this->assertValidationErrorMessage($data, 'stock_quantity', 'Stock quantity cannot be negative');
    }

    #[Test]
    public function category_uuid_must_be_valid_uuid(): void
    {
        $data = $this->createInvalidDataForField('category_uuid', 'invalid-uuid');
        $this->assertValidationFails($data, ['category_uuid']);
    }

    #[Test]
    public function name_cannot_exceed_255_characters(): void
    {
        $data = $this->createInvalidDataForField('name', $this->generateTestString(256));
        $this->assertValidationFails($data, ['name']);
    }

    #[Test]
    public function description_cannot_exceed_1000_characters(): void
    {
        $data = $this->createInvalidDataForField('description', $this->generateTestString(1001));
        $this->assertValidationFails($data, ['description']);
    }

    #[Test]
    public function sku_cannot_exceed_50_characters(): void
    {
        $data = $this->createInvalidDataForField('sku', $this->generateTestString(51));
        $this->assertValidationFails($data, ['sku']);
    }

    #[Test]
    public function image_path_cannot_exceed_500_characters(): void
    {
        $data = $this->createInvalidDataForField('image_path', $this->generateTestString(501));
        $this->assertValidationFails($data, ['image_path']);
    }

    #[Test]
    public function validation_fails_with_non_integer_price(): void
    {
        $data = $this->createInvalidDataForField('price', 'not-a-number');
        $this->assertValidationFails($data, ['price']);
    }

    #[Test]
    public function validation_fails_with_non_integer_stock_quantity(): void
    {
        $data = $this->createInvalidDataForField('stock_quantity', 'not-a-number');
        $this->assertValidationFails($data, ['stock_quantity']);
    }

    #[Test]
    public function validation_fails_with_non_boolean_is_active(): void
    {
        $data = $this->createInvalidDataForField('is_active', 'not-a-boolean');
        $this->assertValidationFails($data, ['is_active']);
    }

    #[Test]
    public function validation_passes_with_boolean_values_for_is_active(): void
    {
        $testCases = [true, false, 1, 0, '1', '0'];
        
        foreach ($testCases as $value) {
            $data = array_merge($this->getValidData(), ['is_active' => $value]);
            $this->assertValidationPasses($data);
        }
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function validation_fails_with_invalid_data(array $data, array $expectedErrors): void
    {
        $this->assertValidationFails($data, $expectedErrors);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'empty data' => [
                [],
                ['name', 'sku', 'price', 'stock_quantity', 'category_uuid']
            ],
            'invalid types' => [
                [
                    'name' => 123,
                    'description' => 456,
                    'sku' => 789,
                    'price' => 'not-number',
                    'stock_quantity' => 'not-number',
                    'image_path' => 123,
                    'is_active' => 'not-boolean',
                    'category_uuid' => 'invalid-uuid'
                ],
                ['price', 'stock_quantity', 'category_uuid']
            ],
        ];
    }
}