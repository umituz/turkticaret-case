<?php

namespace App\DTOs\Profile;

readonly class ProfileUpdateDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['new_password'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ], fn($value) => $value !== null);
    }

    public function hasChanges(): bool
    {
        return $this->name !== null || $this->email !== null || $this->password !== null;
    }
}
