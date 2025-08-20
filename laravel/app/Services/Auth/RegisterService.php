<?php

namespace App\Services\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Repositories\User\UserRepositoryInterface;

class RegisterService
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    public function register(array $data): array
    {
        $userData = RegisterDTO::fromArray($data);
        $user = $this->userRepository->create($userData->toArray());
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
