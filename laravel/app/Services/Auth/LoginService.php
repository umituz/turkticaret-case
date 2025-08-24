<?php

namespace App\Services\Auth;

use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication Login Service for user login operations.
 * 
 * Handles user authentication, credential verification, token generation,
 * and logout operations. Implements secure authentication business logic
 * with proper validation and error handling.
 *
 * @package App\Services\Auth
 */
class LoginService
{
    /**
     * Create a new LoginService instance.
     *
     * @param UserRepositoryInterface $userRepository The user repository for user data operations
     */
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    /**
     * Authenticate user with email and password credentials.
     *
     * @param array $credentials Array containing email and password
     * @return array Array containing authenticated user and access token
     * @throws ValidationException When credentials are invalid or user not found
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

    /**
     * Logout user by revoking their current access token.
     *
     * @param User $user The authenticated user to logout
     * @return bool True if token was successfully revoked, false otherwise
     */
    public function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    /**
     * Find user by email and verify password credentials.
     *
     * @param string $email The email address to lookup
     * @param string $password The plain text password to verify
     * @return User The authenticated user instance
     * @throws ValidationException When user is not found or password is incorrect
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
