<?php

namespace App\Services\User\Address;

use App\DTOs\User\Address\AddressDTO;
use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\User\Address\AddressRepositoryInterface;

class AddressService
{
    public function __construct(protected AddressRepositoryInterface $addressRepository) {}

    public function getUserAddresses(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->addressRepository->findByUser($user);
    }


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

    public function deleteAddressByModel(UserAddress $address): bool
    {
        return $this->addressRepository->delete($address);
    }
}