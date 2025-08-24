<?php

namespace App\Repositories\User;

use App\Models\User\User;
use App\Repositories\Base\BaseRepository;

/**
 * User Repository for user-specific database operations.
 * 
 * Handles user data operations including user lookups by email,
 * authentication queries, and user management functionality.
 * Extends BaseRepository to provide user-specific functionality.
 *
 * @package App\Repositories\User
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new UserRepository instance.
     *
     * @param User $model The User model instance for this repository
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by their email address.
     *
     * @param string $email The email address to search for (case-insensitive)
     * @return User|null The found user or null if not found
     */
    public function findByEmail(string $email): ?User
    {
        $email = strtolower(trim($email));
        return $this->model->where('email', $email)->first();
    }
}
