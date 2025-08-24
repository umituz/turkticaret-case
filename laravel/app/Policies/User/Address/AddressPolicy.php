<?php

namespace App\Policies\User\Address;

use App\Models\User\User;
use App\Models\User\UserAddress;

/**
 * Policy class for UserAddress model authorization.
 * 
 * Handles authorization logic for user address operations including
 * view, create, update, and delete permissions. Ensures users can
 * only access their own addresses.
 *
 * @package App\Policies\User\Address
 */
class AddressPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserAddress $userAddress): bool
    {
        return $user->uuid === $userAddress->user_uuid;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserAddress $userAddress): bool
    {
        return $user->uuid === $userAddress->user_uuid;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserAddress $userAddress): bool
    {
        return $user->uuid === $userAddress->user_uuid;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserAddress $userAddress): bool
    {
        return $user->uuid === $userAddress->user_uuid;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserAddress $userAddress): bool
    {
        return $user->uuid === $userAddress->user_uuid;
    }
}