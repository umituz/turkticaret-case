<?php

namespace App\DTOs\User;

readonly class UserPreferencesDTO
{
    public function __construct(
        public string $languageUuid,
        public string $timezone,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            languageUuid: $data['language_uuid'],
            timezone: $data['timezone'],
        );
    }

    public function toArray(): array
    {
        return [
            'language_uuid' => $this->languageUuid,
            'timezone' => $this->timezone,
        ];
    }
}