<?php

namespace App\DTOs\Profile;

/**
 * Data Transfer Object for profile update operations.
 * 
 * This DTO handles partial profile updates, allowing users to update
 * individual fields (name, email, password) without requiring all fields.
 * Provides validation and array conversion capabilities.
 *
 * @package App\DTOs\Profile
 */
readonly class ProfileUpdateDTO
{
    /**
     * Create a new profile update DTO instance.
     *
     * @param string|null $name New name for the user profile
     * @param string|null $email New email address for the user
     * @param string|null $password New password for the user
     */
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
    ) {}

    /**
     * Create DTO instance from array data.
     *
     * @param array $data Array containing profile update data
     * @return self New ProfileUpdateDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['new_password'] ?? null,
        );
    }

    /**
     * Convert DTO to array format, filtering out null values.
     *
     * @return array Array representation of profile updates (only non-null values)
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ], fn($value) => $value !== null);
    }

    /**
     * Check if the DTO contains any changes to apply.
     *
     * @return bool True if at least one field has a value to update
     */
    public function hasChanges(): bool
    {
        return $this->name !== null || $this->email !== null || $this->password !== null;
    }
}
