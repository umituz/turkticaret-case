<?php

namespace App\Services\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Jobs\User\SendWelcomeEmailJob;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Country\CountryService;
use App\Services\Language\LanguageService;

class RegisterService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected CountryService $countryService,
        protected LanguageService $languageService
    ) {}

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
