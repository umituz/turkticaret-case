<?php

namespace Tests\Traits;

use Mockery;
use Mockery\MockInterface;

/**
 * Trait providing common mocking utilities for request unit tests
 */
trait RequestMocksTrait
{
    /**
     * Mock a form request with specific data
     */
    protected function mockFormRequest(string $requestClass, array $data = []): MockInterface
    {
        $request = Mockery::mock($requestClass);
        
        $request->shouldReceive('all')->andReturn($data);
        $request->shouldReceive('input')->andReturnUsing(function ($key, $default = null) use ($data) {
            return $data[$key] ?? $default;
        });
        $request->shouldReceive('has')->andReturnUsing(function ($key) use ($data) {
            return array_key_exists($key, $data);
        });
        $request->shouldReceive('filled')->andReturnUsing(function ($key) use ($data) {
            return isset($data[$key]) && !empty($data[$key]);
        });
        
        return $request;
    }

    /**
     * Create test data with overrides
     */
    protected function createTestRequestData(array $overrides = []): array
    {
        $defaults = [
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'created_at' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
            'updated_at' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create invalid test data for specific field
     */
    protected function createInvalidDataForField(string $field, $invalidValue): array
    {
        $validData = $this->getValidData();
        $validData[$field] = $invalidValue;
        return $validData;
    }

    /**
     * Create test data missing specific field
     */
    protected function createDataMissingField(string $field): array
    {
        $validData = $this->getValidData();
        unset($validData[$field]);
        return $validData;
    }

    /**
     * Generate test email addresses
     */
    protected function generateTestEmails(int $count = 1): array
    {
        $emails = [];
        for ($i = 1; $i <= $count; $i++) {
            $emails[] = "test{$i}@example.com";
        }
        return $count === 1 ? $emails[0] : $emails;
    }

    /**
     * Generate test UUIDs
     */
    protected function generateTestUuids(int $count = 1): array
    {
        $uuids = [];
        for ($i = 0; $i < $count; $i++) {
            $uuids[] = $this->generateTestUuid();
        }
        return $count === 1 ? $uuids[0] : $uuids;
    }

    /**
     * Create test password
     */
    protected function generateTestPassword(int $length = 8): string
    {
        return str_repeat('Test123!', max(1, (int) ceil($length / 8)));
    }

    /**
     * Create test strings of specific length
     */
    protected function generateTestString(int $length): string
    {
        return str_repeat('a', $length);
    }

    /**
     * Create test numeric values
     */
    protected function generateTestNumber(int $min = 1, int $max = 1000): int
    {
        return rand($min, $max);
    }

    /**
     * Mock validation failure scenario
     */
    protected function mockValidationFailure(array $errors): void
    {
        $validator = Mockery::mock();
        $validator->shouldReceive('fails')->andReturn(true);
        $validator->shouldReceive('passes')->andReturn(false);
        $validator->shouldReceive('errors')->andReturn(collect($errors));
        
        $this->app->instance('validator', $validator);
    }

    /**
     * Mock validation success scenario
     */
    protected function mockValidationSuccess(): void
    {
        $validator = Mockery::mock();
        $validator->shouldReceive('fails')->andReturn(false);
        $validator->shouldReceive('passes')->andReturn(true);
        $validator->shouldReceive('errors')->andReturn(collect([]));
        
        $this->app->instance('validator', $validator);
    }

    /**
     * Create test data for different scenarios
     */
    protected function getTestDataScenarios(): array
    {
        return [
            'valid_minimal' => $this->getValidMinimalData(),
            'valid_complete' => $this->getValidCompleteData(),
            'invalid_empty' => [],
            'invalid_types' => $this->getInvalidTypeData(),
        ];
    }

    /**
     * Get minimal valid data (override in test classes)
     */
    protected function getValidMinimalData(): array
    {
        return $this->getValidData();
    }

    /**
     * Get complete valid data (override in test classes)
     */
    protected function getValidCompleteData(): array
    {
        return $this->getValidData();
    }

    /**
     * Get data with invalid types (override in test classes)
     */
    protected function getInvalidTypeData(): array
    {
        return [];
    }

    /**
     * Abstract method that must be implemented by test classes
     */
    abstract protected function getValidData(): array;
}