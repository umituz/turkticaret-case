<?php

namespace App\Repositories\Base;

/**
 * Base repository interface combining read, write, and soft delete operations.
 *
 * This interface extends all specialized interfaces to provide a complete
 * repository contract for models that need full CRUD and soft delete capabilities.
 * Individual repositories can implement specific interfaces based on their needs.
 *
 * @package App\Repositories\Base
 */
interface BaseRepositoryInterface extends ReadRepositoryInterface, WriteRepositoryInterface, SoftDeleteRepositoryInterface
{
    // This interf ace combines all repository capabilities
    // - ReadRepositoryInterface for read-only repositories
    // - WriteRepositoryInterface for write operations
    // - SoftDeleteRepositoryInterface for soft deletable models
    // - BaseRepositoryInterface for full CRUD operations
}
