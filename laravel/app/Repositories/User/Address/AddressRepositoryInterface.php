<?php

namespace App\Repositories\User\Address;

use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface AddressRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUser(User $user): Collection;
    
    public function update(UserAddress $address, array $data): UserAddress;
    
    public function delete(UserAddress $address): bool;
    
    public function unsetDefaultAddresses(User $user, ?string $type = null): void;
}