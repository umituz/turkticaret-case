<?php

namespace App\Services\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Jobs\User\SendWelcomeEmailJob;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Country\CountryService;
use App\Services\Language\LanguageService;

/**
 * User Registration Service for new account creation.
 * 
 * Handles comprehensive user registration including account creation,
 * country and language assignment, token generation, and welcome email dispatch.
 * Implements business rules for new user onboarding.
 *
 * @package App\Services\Auth
 */
class RegisterService
{
    /**
     * Create a new RegisterService instance.
     *
     * @param UserRepositoryInterface $userRepository The user repository for user data operations
     * @param CountryService $countryService The country service for country operations
     * @param LanguageService $languageService The language service for language operations
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected CountryService $countryService,
        protected LanguageService $languageService
    ) {}

    /**
     * Register a new user account with complete onboarding.
     *
     * @param array $data Registration data including name, email, password, and country code
     * @return array Array containing created user instance and access token
     */
    public function register(array $data): array
    {
        $userData = RegisterDTO::fromArray($data);
        $country = $this->countryService->findByCode($userData->countryCode);
        $language = $this->languageService->getByCountryLocale($country->locale);

        $user = $this->userRepository->create([
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => $userData->password,
            'country_uuid' => $country->uuid,
            'language_uuid' => $language->uuid,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        SendWelcomeEmailJob::dispatch($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
