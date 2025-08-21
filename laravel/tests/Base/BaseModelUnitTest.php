<?php

namespace Tests\Base;

use Tests\Base\UnitTestCase;
use Tests\Traits\ModelAssertionsTrait;
use Tests\Traits\ModelMocksTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Base test case for model unit tests
 * Provides minimal helper functionality for model testing without database connections
 * All assertion methods are in ModelAssertionsTrait following principles
 * All mock methods are in ModelMocksTrait following principles
 */
abstract class BaseModelUnitTest extends UnitTestCase
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
     * Set up the test model instance
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ($this->getModelClass());
    }
}