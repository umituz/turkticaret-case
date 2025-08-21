<?php

namespace Tests\Base;

use Tests\Base\UnitTestCase;
use Tests\Traits\RepositoryAssertionsTrait;
use Tests\Traits\RepositoryMocksTrait;

/**
 * Base test case for repository unit tests
 * Provides minimal helper methods for testing repository data access logic
 */
abstract class BaseRepositoryUnitTest extends UnitTestCase
{
    use RepositoryAssertionsTrait, RepositoryMocksTrait;

    /**
     * The repository instance to test
     */
    protected $repository;

    /**
     * Get the repository class being tested
     */
    abstract protected function getRepositoryClass(): string;

    /**
     * Get the model class used by the repository
     */
    abstract protected function getModelClass(): string;

    /**
     * Set up dependencies for the repository
     * Override this method to inject dependencies
     */
    protected function getRepositoryDependencies(): array
    {
        $model = $this->mockModel($this->getModelClass());
        return [$model];
    }

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $repositoryClass = $this->getRepositoryClass();
        $dependencies = $this->getRepositoryDependencies();
        
        $this->repository = new $repositoryClass(...$dependencies);
    }
}