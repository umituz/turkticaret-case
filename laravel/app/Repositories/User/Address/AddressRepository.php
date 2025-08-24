<?php

namespace App\Repositories\User\Address;

use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Address Repository for user address-specific database operations.
 * 
 * Handles user address management including retrieving user addresses,
 * updating address information, deleting addresses, and managing default
 * address settings. Extends BaseRepository to provide address-specific functionality.
 *
 * @package App\Repositories\User\Address
 */
class AddressRepository extends BaseRepository implements AddressRepositoryInterface
{
    /**
     * Create a new AddressRepository instance.
     *
     * @param UserAddress $model The UserAddress model instance for this repository
     */
    public function __construct(UserAddress $model)
    {
        parent::__construct($model);
    }

    /**
     * Find all addresses for a specific user.
     *
     * @param User $user The user to find addresses for
     * @return Collection Collection of user addresses with loaded country relationships, ordered by default status and creation date
     */
    public function findByUser(User $user): Collection
    {
        return $this->model->where('user_uuid', $user->uuid)
            ->with('country')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }


    /**
     * Update a specific address with new data.
     *
     * @param UserAddress $address The address to update
     * @param array $data Array of data to update the address with
     * @return UserAddress The updated address instance
     */
    public function update(UserAddress $address, array $data): UserAddress
    {
        $address->update($data);
        return $address;
    }

    /**
     * Delete a specific address.
     *
     * @param UserAddress $address The address to delete
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete(UserAddress $address): bool
    {
        return $address->delete();
    }

    /**
     * Unset default status from user's addresses.
     *
     * @param User $user The user whose default addresses should be unset
     * @param string|null $type Optional address type filter to unset only specific address types
     * @return void
     */
    public function unsetDefaultAddresses(User $user, ?string $type = null): void
    {
        $query = $this->model->where('user_uuid', $user->uuid)->where('is_default', true);

        if ($type) {
            $query->where('type', $type);
        }

        $query->update(['is_default' => false]);
    }
}
