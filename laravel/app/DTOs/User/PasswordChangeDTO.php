<?php

namespace App\DTOs\User;

/**
 * Data Transfer Object for password change operations
 * 
 * Handles secure password change requests by encapsulating both the current
 * and new password data. This DTO ensures type safety and provides a clean
 * interface for password update operations while maintaining security best practices.
 * 
 * @property string $currentPassword User's current password for verification
 * @property string $newPassword User's new password to be set
 */
readonly class PasswordChangeDTO
{
    /**
     * Create a new PasswordChangeDTO instance
     * 
     * Both passwords are handled as plain text within the DTO and should be
     * verified/hashed by the service layer during the change operation.
     * 
     * @param string $currentPassword Current password for authentication
     * @param string $newPassword New password to replace the current one
     */
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {}

    /**
     * Create PasswordChangeDTO from request array
     * 
     * Factory method to create an instance from validated request data.
     * Expects snake_case keys from the request and maps them to the DTO properties.
     * 
     * @param array $data Validated request data containing password fields
     * @return self New PasswordChangeDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword: $data['new_password'],
        );
    }
}