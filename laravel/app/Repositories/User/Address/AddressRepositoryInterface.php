<?php

namespace App\Repositories\User\Address;

use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for Address repository implementations.
 * 
 * Defines the required methods for User Address data access layer operations
 * including address CRUD operations, user-specific address management,
 * and default address handling.
 *
 * @package App\Repositories\User\Address
 */
interface AddressRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find all addresses belonging to a specific user.
     *
     * @param User $user The user instance to search addresses for
     * @return Collection<int, UserAddress> Collection of user addresses
     */
    public function findByUser(User $user): Collection;
    
    /**
     * Update an existing address with new data.
     *
     * @param UserAddress $address The address instance to update
     * @param array<string, mixed> $data Array of data to update
     * @return UserAddress The updated address instance
     */
    public function update(UserAddress $address, array $data): UserAddress;
    
    /**
     * Delete an address from the database.
     *
     * @param UserAddress $address The address instance to delete
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete(UserAddress $address): bool;
    
    /**
     * Remove default status from user addresses.
     *
     * @param User $user The user whose addresses should be updated
     * @param string|null $type Optional address type filter (billing, shipping)
     * @return void
     */
    public function unsetDefaultAddresses(User $user, ?string $type = null): void;
}