<?php

declare(strict_types=1);

namespace Tests\Traits;

use ReflectionClass;

/**
 * Trait providing common assertions for model unit tests
 */
trait ModelAssertionsTrait
{
    /**
     * Assert that the model has the expected fillable attributes
     */
    protected function assertHasFillable(array $expectedFillable): void
    {
        $this->assertEquals($expectedFillable, $this->model->getFillable());
    }

    /**
     * Assert that the model casts attributes correctly
     */
    protected function assertHasCasts(array $expectedCasts): void
    {
        $modelCasts = $this->model->getCasts();

        foreach ($expectedCasts as $attribute => $type) {
            $this->assertArrayHasKey($attribute, $modelCasts);
            $this->assertEquals($type, $modelCasts[$attribute]);
        }
    }

    /**
     * Assert that the model has the expected attribute value
     */
    protected function assertAttributeEquals(string $attribute, $value): void
    {
        $this->model->$attribute = $value;
        $this->assertEquals($value, $this->model->$attribute);
    }

    /**
     * Assert that model has expected traits (including inherited traits)
     */
    protected function assertHasTraits(array $expectedTraits): void
    {
        $reflection = new ReflectionClass($this->model);
        $allTraits = [];
        
        // Get traits from the current class
        $allTraits = array_merge($allTraits, $reflection->getTraitNames());
        
        // Get traits from parent classes
        $parent = $reflection->getParentClass();
        while ($parent) {
            $allTraits = array_merge($allTraits, $parent->getTraitNames());
            $parent = $parent->getParentClass();
        }

        $allTraits = array_unique($allTraits);

        foreach ($expectedTraits as $trait) {
            $this->assertContains(
                $trait,
                $allTraits,
                "Model does not use expected trait: {$trait}"
            );
        }
    }

    /**
     * Assert that model extends expected base class
     */
    protected function assertExtendsBaseClass(string $expectedBaseClass): void
    {
        $this->assertInstanceOf(
            $expectedBaseClass,
            $this->model,
            "Model does not extend expected base class: {$expectedBaseClass}"
        );
    }

    /**
     * Assert that model has expected table name
     */
    protected function assertHasTable(string $expectedTable): void
    {
        $this->assertEquals(
            $expectedTable,
            $this->model->getTable(),
            "Model table name mismatch"
        );
    }

    /**
     * Assert that model has expected primary key
     */
    protected function assertHasPrimaryKey(string $expectedKey): void
    {
        $this->assertEquals(
            $expectedKey,
            $this->model->getKeyName(),
            "Model primary key mismatch"
        );
    }

    /**
     * Assert that model uses timestamps
     */
    protected function assertUsesTimestamps(bool $expected = true): void
    {
        $this->assertEquals(
            $expected,
            $this->model->usesTimestamps(),
            $expected ? "Model should use timestamps" : "Model should not use timestamps"
        );
    }

    /**
     * Assert that model uses soft deletes
     */
    protected function assertUsesSoftDeletes(): void
    {
        $this->assertHasTraits([
            'Illuminate\Database\Eloquent\SoftDeletes'
        ]);
    }

    /**
     * Assert that model has expected hidden attributes
     */
    protected function assertHasHidden(array $expectedHidden): void
    {
        $this->assertEquals(
            $expectedHidden,
            $this->model->getHidden(),
            "Model hidden attributes mismatch"
        );
    }

    /**
     * Assert that model has expected guarded attributes
     */
    protected function assertHasGuarded(array $expectedGuarded): void
    {
        $this->assertEquals(
            $expectedGuarded,
            $this->model->getGuarded(),
            "Model guarded attributes mismatch"
        );
    }

    /**
     * Assert that enum casting works correctly
     */
    protected function assertEnumCasting(string $attribute, string $enumClass, $value): void
    {
        $casts = $this->model->getCasts();

        $this->assertArrayHasKey($attribute, $casts, "Attribute {$attribute} is not cast");
        $this->assertEquals($enumClass, $casts[$attribute], "Enum casting mismatch for {$attribute}");

        if ($value !== null) {
            $this->model->setAttribute($attribute, $value);
            $castedValue = $this->model->getAttribute($attribute);

            if (is_string($value) && enum_exists($enumClass)) {
                $this->assertInstanceOf($enumClass, $castedValue);
            }
        }
    }

    /**
     * Assert that model has UUID primary key setup
     */
    protected function assertUsesUuidPrimaryKey(): void
    {
        $this->assertHasTraits([
            'Illuminate\Database\Eloquent\Concerns\HasUuids'
        ]);

        $this->assertEquals('uuid', $this->model->getKeyName());
        $this->assertFalse($this->model->getIncrementing());
        $this->assertEquals('string', $this->model->getKeyType());
    }

    /**
     * Assert that a relationship method exists
     */
    protected function assertHasRelationshipMethod(string $methodName): void
    {
        $this->assertTrue(
            method_exists($this->model, $methodName),
            "Relationship method {$methodName} does not exist on " . get_class($this->model)
        );
    }

    /**
     * Assert that model can be created with factory
     */
    protected function assertHasFactory(): void
    {
        $modelClass = get_class($this->model);
        $this->assertTrue(
            method_exists($this->model, 'factory'),
            "Model {$modelClass} should have factory method"
        );
    }

    /**
     * Assert that a protected property equals expected value
     */
    protected function assertProtectedPropertyEquals(string $propertyName, $expectedValue): void
    {
        $reflection = new ReflectionClass($this->model);
        
        if ($reflection->hasProperty($propertyName)) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $actualValue = $property->getValue($this->model);
            
            $this->assertEquals(
                $expectedValue,
                $actualValue,
                "Protected property {$propertyName} does not match expected value"
            );
        } else {
            $this->fail("Property {$propertyName} does not exist on model " . get_class($this->model));
        }
    }
}