<?php

namespace App\Repositories\User\Address;

use App\Models\User\User;
use App\Models\User\UserAddress;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository extends BaseRepository implements AddressRepositoryInterface
{
    public function __construct(UserAddress $model)
    {
        parent::__construct($model);
    }

    public function findByUser(User $user): Collection
    {
        return $this->model->where('user_uuid', $user->uuid)
            ->with('country')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }


    public function update(UserAddress $address, array $data): UserAddress
    {
        $address->update($data);
        return $address;
    }

    public function delete(UserAddress $address): bool
    {
        return $address->delete();
    }

    public function unsetDefaultAddresses(User $user, ?string $type = null): void
    {
        $query = $this->model->where('user_uuid', $user->uuid)->where('is_default', true);

        if ($type) {
            $query->where('type', $type);
        }

        $query->update(['is_default' => false]);
    }
}
