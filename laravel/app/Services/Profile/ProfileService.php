<?php

namespace App\Services\Profile;

use App\DTOs\Profile\ProfileUpdateDTO;
use App\Models\Auth\User;
use App\Repositories\User\UserRepositoryInterface;

class ProfileService
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    public function getProfile(User $user): User
    {
        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $updateData = ProfileUpdateDTO::fromArray($data);
        $this->userRepository->updateByUuid($user->uuid, $updateData->toArray());

        return $this->userRepository->findByUuid($user->uuid);
    }
}
