<?php

namespace Tests\Base;

use Tests\Traits\ModelAssertionsTrait;
use Tests\Traits\ModelMocksTrait;
use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Base test case for Authority model unit tests (Role and Permission)
 * Special handling for Spatie Permission package models
 */
abstract class BaseAuthorityModelUnitTest extends UnitTestCase
{
    use ModelAssertionsTrait, ModelMocksTrait;

    /**
     * The model instance to test
     */
    protected Model $model;

    /**
     * Get the model class being tested
     */
    abstract protected function getModelClass(): string;

    /**
     * Set up the test model instance with special Authority model handling
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up special guard handling for Authority models
        $this->setupAuthorityModelGuards();

        // Create model with proper guard setup
        $modelClass = $this->getModelClass();
        $this->model = new $modelClass();

        // Mock guard_name if needed
        if (method_exists($this->model, 'setGuardName')) {
            $this->model->setGuardName('web');
        }
    }

    /**
     * Setup guards specifically for Authority models
     */
    protected function setupAuthorityModelGuards(): void
    {
        // Mock the Guard class directly
        $guardMock = Mockery::mock('alias:' . \Spatie\Permission\Guard::class);
        $guardMock->shouldReceive('getDefaultName')
            ->andReturn('web');
        $guardMock->shouldReceive('getNames')
            ->andReturn(collect(['web', 'api']));
    }
}
