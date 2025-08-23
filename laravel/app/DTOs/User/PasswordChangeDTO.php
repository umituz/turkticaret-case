<?php

namespace App\DTOs\User;

readonly class PasswordChangeDTO
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword: $data['new_password'],
        );
    }
}