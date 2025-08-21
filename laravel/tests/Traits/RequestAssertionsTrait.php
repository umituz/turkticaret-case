<?php

namespace Tests\Traits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

/**
 * Trait providing common assertions for request unit tests
 */
trait RequestAssertionsTrait
{
    /**
     * Assert that request has a specific method
     */
    protected function assertRequestHasMethod(string $method): void
    {
        $this->assertTrue(
            method_exists($this->request, $method),
            "Request does not have method: {$method}"
        );
    }

    /**
     * Assert that request has expected constructor dependencies
     */
    protected function assertHasRequestConstructorDependencies(array $expectedDependencies): void
    {
        $reflection = new \ReflectionClass($this->request);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            $this->assertEmpty($expectedDependencies, 'Request should not have constructor dependencies');
            return;
        }

        $parameters = $constructor->getParameters();
        $this->assertCount(count($expectedDependencies), $parameters);

        foreach ($parameters as $index => $parameter) {
            $type = $parameter->getType();
            $expectedType = $expectedDependencies[$index];
            
            if ($type && !$type->isBuiltin()) {
                $this->assertEquals($expectedType, $type->getName());
            }
        }
    }

    /**
     * Assert that request validation passes with valid data
     */
    protected function assertValidationPasses(array $data): void
    {
        // Skip unique/exists validation rules for unit tests
        $rules = $this->request->rules();
        $filteredRules = $this->filterDatabaseRules($rules);
        
        $validator = Validator::make($data, $filteredRules, $this->request->messages());
        
        $this->assertTrue(
            $validator->passes(),
            'Validation should pass but failed with errors: ' . json_encode($validator->errors()->toArray())
        );
    }

    /**
     * Assert that request validation fails with invalid data
     */
    protected function assertValidationFails(array $data, array $expectedErrorFields = []): void
    {
        // Skip unique/exists validation rules for unit tests
        $rules = $this->request->rules();
        $filteredRules = $this->filterDatabaseRules($rules);
        
        $validator = Validator::make($data, $filteredRules, $this->request->messages());
        
        $this->assertTrue(
            $validator->fails(),
            'Validation should fail but passed for data: ' . json_encode($data)
        );

        if (!empty($expectedErrorFields)) {
            $errors = $validator->errors();
            foreach ($expectedErrorFields as $field) {
                $this->assertTrue(
                    $errors->has($field),
                    "Expected validation error for field: {$field}"
                );
            }
        }
    }

    /**
     * Assert that request has specific validation rule for field
     */
    protected function assertHasValidationRule(string $field, string $rule): void
    {
        $rules = $this->request->rules();
        
        $this->assertArrayHasKey($field, $rules, "Field {$field} should have validation rules");
        
        $fieldRules = is_array($rules[$field]) ? $rules[$field] : explode('|', $rules[$field]);
        
        $this->assertContains(
            $rule,
            $fieldRules,
            "Field {$field} should have rule: {$rule}"
        );
    }

    /**
     * Assert that request has specific validation message for field and rule
     */
    protected function assertHasValidationMessage(string $key, string $expectedMessage): void
    {
        $messages = $this->request->messages();
        
        $this->assertArrayHasKey($key, $messages, "Should have custom message for: {$key}");
        $this->assertEquals($expectedMessage, $messages[$key], "Message mismatch for: {$key}");
    }

    /**
     * Assert that request authorization returns expected result
     */
    protected function assertAuthorizationResult(bool $expected): void
    {
        $result = $this->request->authorize();
        
        $this->assertEquals(
            $expected,
            $result,
            'Authorization result should be ' . ($expected ? 'true' : 'false')
        );
    }

    /**
     * Assert that request is instance of FormRequest
     */
    protected function assertIsFormRequest(): void
    {
        $this->assertInstanceOf(
            FormRequest::class,
            $this->request,
            'Request should extend FormRequest'
        );
    }

    /**
     * Assert that validation rules array is not empty
     */
    protected function assertHasValidationRules(): void
    {
        $rules = $this->request->rules();
        
        $this->assertIsArray($rules, 'Rules should return an array');
        $this->assertNotEmpty($rules, 'Rules should not be empty');
    }

    /**
     * Assert that validation messages array is not empty
     */
    protected function assertHasValidationMessages(): void
    {
        $messages = $this->request->messages();
        
        $this->assertIsArray($messages, 'Messages should return an array');
        $this->assertNotEmpty($messages, 'Messages should not be empty');
    }

    /**
     * Assert specific validation error message
     */
    protected function assertValidationErrorMessage(array $data, string $field, string $expectedMessage): void
    {
        // Skip unique/exists validation rules for unit tests
        $rules = $this->request->rules();
        $filteredRules = $this->filterDatabaseRules($rules);
        
        $validator = Validator::make($data, $filteredRules, $this->request->messages());
        
        $this->assertTrue($validator->fails(), 'Validation should fail');
        
        $errors = $validator->errors();
        $this->assertTrue($errors->has($field), "Should have error for field: {$field}");
        
        $actualMessages = $errors->get($field);
        $this->assertContains(
            $expectedMessage,
            $actualMessages,
            "Error message should contain: {$expectedMessage}"
        );
    }

    /**
     * Assert that field is required
     */
    protected function assertFieldIsRequired(string $field): void
    {
        $this->assertHasValidationRule($field, 'required');
        
        // Test with empty data
        $this->assertValidationFails([], [$field]);
    }

    /**
     * Assert that field is optional
     */
    protected function assertFieldIsOptional(string $field): void
    {
        $rules = $this->request->rules();
        
        if (isset($rules[$field])) {
            $fieldRules = is_array($rules[$field]) ? $rules[$field] : explode('|', $rules[$field]);
            $this->assertNotContains(
                'required',
                $fieldRules,
                "Field {$field} should not be required"
            );
        }
        
        // Test validation passes without the field
        $validData = $this->getValidData();
        unset($validData[$field]);
        $this->assertValidationPasses($validData);
    }

    /**
     * Get valid test data (must be implemented by test class)
     */
    abstract protected function getValidData(): array;

    /**
     * Filter out database-dependent validation rules for unit tests
     */
    protected function filterDatabaseRules(array $rules): array
    {
        $filtered = [];
        
        foreach ($rules as $field => $fieldRules) {
            $rulesArray = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            
            // Remove database-dependent rules
            $filteredFieldRules = array_filter($rulesArray, function ($rule) {
                return !str_starts_with($rule, 'unique:') && 
                       !str_starts_with($rule, 'exists:');
            });
            
            $filtered[$field] = $filteredFieldRules;
        }
        
        return $filtered;
    }
}