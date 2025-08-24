<?php

namespace App\Repositories\User;

use App\Models\User\User;
use App\Repositories\Base\BaseRepositoryInterface;

/**
 * User Repository Interface
 * 
 * Defines the contract for user data access operations.
 * Extends the base repository interface with user-specific methods
 * for authentication, profile management, and user queries.
 * 
 * @package App\Repositories\User
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email address
     * 
     * Retrieves a user record by their email address.
     * Used primarily for authentication and user lookups.
     * 
     * @param string $email The user's email address
     * @return User|null The user if found, null otherwise
     */
    public function findByEmail(string $email): ?User;
}
