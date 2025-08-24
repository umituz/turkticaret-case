<?php

namespace App\Services\User\Address;

use App\DTOs\User\Address\AddressDTO;
use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\User\Address\AddressRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Address Service for address management operations.
 *
 * Handles CRUD operations for user addresses including creation, updating,
 * deletion, and retrieval with default address management. Ensures proper
 * address validation and default address handling.
 *
 * @package App\Services\User\Address
 */
class AddressService
{
    /**
     * Create a new AddressService instance.
     *
     * @param AddressRepositoryInterface $addressRepository The address repository for data operations
     */
    public function __construct(protected AddressRepositoryInterface $addressRepository) {}

    /**
     * Get all addresses for a specific user.
     *
     * @param User $user The user to get addresses for
     * @return Collection Collection of user addresses
     */
    public function getUserAddresses(User $user): Collection
    {
        return $this->addressRepository->findByUser($user);
    }


    /**
     * Create a new address for a user with default address management.
     *
     * @param User $user The user to create address for
     * @param array $data Address data including street, city, postal code, etc.
     * @return UserAddress The newly created address instance
     */
    public function createAddress(User $user, array $data): UserAddress
    {
        $addressData = AddressDTO::forCreate($data);

        // If this is set as default, unset other defaults
        if ($addressData->is_default) {
            $this->addressRepository->unsetDefaultAddresses($user);
        }

        return $this->addressRepository->create([
            ...$addressData->toArray(),
            'user_uuid' => $user->uuid,
        ]);
    }


    /**
     * Update an existing address with default address management.
     *
     * @param UserAddress $address The address to update
     * @param array $data Updated address data
     * @return UserAddress The updated address instance
     */
    public function updateAddressByModel(UserAddress $address, array $data): UserAddress
    {
        $addressData = AddressDTO::forUpdate($data);

        // If this is set as default, unset other defaults for this user
        if ($addressData->is_default) {
            $this->addressRepository->unsetDefaultAddresses($address->user);
        }

        $this->addressRepository->update($address, $addressData->toFilteredArray());

        return $address->fresh();
    }

    /**
     * Delete a user address.
     *
     * @param UserAddress $address The address to delete
     * @return bool True if deletion was successful, false otherwise
     */
    public function deleteAddressByModel(UserAddress $address): bool
    {
        return $this->addressRepository->delete($address);
    }
}
