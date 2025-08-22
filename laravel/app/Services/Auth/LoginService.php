<?php

namespace App\Services\Auth;

use App\Models\Auth\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    /**
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = $this->findAndVerifyUser($credentials['email'], $credentials['password']);
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    /**
     * @throws ValidationException
     */
    protected function findAndVerifyUser(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user;
    }
}
