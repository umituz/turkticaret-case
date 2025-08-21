<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Mocks Trait for Unit Tests
 *
 * Contains all mock creation methods for model unit testing
 * following single responsibility principle
 */
trait ModelMocksTrait
{
    /**
     * Create a model instance for testing
     */
    protected function createModelInstance(string $modelClass, array $attributes = []): Model
    {
        $instance = new $modelClass();

        foreach ($attributes as $key => $value) {
            $instance->setAttribute($key, $value);
        }

        return $instance;
    }

    /**
     * Create a model instance with relationships
     */
    protected function createModelInstanceWithRelationships(string $modelClass, array $attributes = [], array $relationships = []): Model
    {
        $instance = $this->createModelInstance($modelClass, $attributes);

        foreach ($relationships as $relationName => $relationValue) {
            $instance->setRelation($relationName, $relationValue);
        }

        return $instance;
    }

    /**
     * Get model's fillable attributes
     */
    protected function getModelFillable(): array
    {
        return $this->model->getFillable();
    }

    /**
     * Get model's casts
     */
    protected function getModelCasts(): array
    {
        return $this->model->getCasts();
    }

    /**
     * Get model's table name
     */
    protected function getModelTable(): string
    {
        return $this->model->getTable();
    }

    /**
     * Get model's primary key name
     */
    protected function getModelKeyName(): string
    {
        return $this->model->getKeyName();
    }

    /**
     * Check if model uses timestamps
     */
    protected function modelUsesTimestamps(): bool
    {
        return $this->model->usesTimestamps();
    }

    /**
     * Get model's hidden attributes
     */
    protected function getModelHidden(): array
    {
        return $this->model->getHidden();
    }

    /**
     * Get model's guarded attributes
     */
    protected function getModelGuarded(): array
    {
        return $this->model->getGuarded();
    }
}