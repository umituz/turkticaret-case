<?php

namespace App\DTOs\User;

/**
 * Data Transfer Object for user preferences data.
 * 
 * This DTO encapsulates user preference information including language
 * and timezone settings. It provides methods for array conversion and
 * ensures type safety with readonly properties.
 *
 * @package App\DTOs\User
 */
readonly class UserPreferencesDTO
{
    /**
     * Create a new user preferences DTO instance.
     *
     * @param string $languageUuid UUID of the user's preferred language
     * @param string $timezone User's preferred timezone
     */
    public function __construct(
        public string $languageUuid,
        public string $timezone,
    ) {}

    /**
     * Create DTO instance from array data.
     *
     * @param array $data Array containing user preferences data
     * @return self New UserPreferencesDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            languageUuid: $data['language_uuid'],
            timezone: $data['timezone'],
        );
    }

    /**
     * Convert DTO to array format.
     *
     * @return array Array representation of user preferences
     */
    public function toArray(): array
    {
        return [
            'language_uuid' => $this->languageUuid,
            'timezone' => $this->timezone,
        ];
    }
}