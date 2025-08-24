<?php

namespace App\DTOs\Auth;

/**
 * Data Transfer Object for user registration
 * 
 * Handles the transfer and validation of user registration data between
 * the presentation layer and the domain layer. This DTO ensures type safety
 * and provides a clean interface for registration operations.
 * 
 * @property string $name User's full name (required)
 * @property string $email User's email address for authentication (required)
 * @property string $password User's password (will be hashed before storage) (required)
 * @property string|null $countryCode ISO country code for user localization (optional)
 */
readonly class RegisterDTO
{
    /**
     * Create a new RegisterDTO instance
     * 
     * @param string $name User's full name
     * @param string $email User's email address (must be unique)
     * @param string $password User's password (plain text, will be hashed)
     * @param string|null $countryCode ISO country code (e.g., 'TR', 'US')
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $countryCode = null,
    ) {}

    /**
     * Create RegisterDTO from request array
     * 
     * Factory method to create a RegisterDTO instance from validated request data.
     * Handles key transformation from snake_case to camelCase properties.
     * 
     * @param array $data Validated request data containing registration fields
     * @return self New RegisterDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            countryCode: $data['country_code'] ?? null,
        );
    }

    /**
     * Convert DTO to array for database operations
     * 
     * Transforms the DTO properties back to snake_case format suitable for
     * database operations and API responses. Password is included as-is
     * and should be hashed by the service layer before storage.
     * 
     * @return array Array representation with snake_case keys
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'country_code' => $this->countryCode,
        ];
    }
}