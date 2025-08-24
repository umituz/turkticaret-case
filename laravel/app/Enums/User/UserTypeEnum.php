<?php

namespace App\Enums\User;

/**
 * User type enumeration for role-based access control
 * 
 * Defines the available user roles in the system and their associated
 * permissions. This enum is used throughout the application to enforce
 * role-based access control and determine user capabilities.
 * 
 * @method static self ADMIN() Administrator role with full system access
 * @method static self USER() Regular user role with limited access
 */
enum UserTypeEnum: string
{
    /**
     * Administrator role
     * Has full access to all system features including user management,
     * product management, order management, and system configuration.
     */
    case ADMIN = 'admin';
    
    /**
     * Regular user role
     * Limited to personal account management, shopping features,
     * and viewing their own orders and data.
     */
    case USER = 'user';

    /**
     * Get all available user type values
     * 
     * Returns an array of string values for all user types.
     * Useful for validation rules and select options.
     * 
     * @return array Array of user type values ['admin', 'user']
     */
    public static function getAvailableTypes(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label for the user type
     * 
     * Provides display-friendly names for UI presentation.
     * 
     * @return string Formatted label for the user type
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'Regular User',
        };
    }

    /**
     * Get default email address for the user type
     * 
     * Returns predefined email addresses used for testing and seeding.
     * These are the default emails used in the authentication system.
     * 
     * @return string Default email address for the user type
     */
    public function getEmail(): string
    {
        return match ($this) {
            self::ADMIN => 'admin@test.com',
            self::USER => 'user@test.com',
        };
    }

    /**
     * Check if user type has administrative access
     * 
     * Determines whether this user type has access to admin-only features.
     * 
     * @return bool True if user has admin access, false otherwise
     */
    public function hasAdminAccess(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if user type can manage orders
     * 
     * Determines whether this user type can view and manage all orders
     * in the system, not just their own.
     * 
     * @return bool True if user can manage all orders
     */
    public function canManageOrders(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if user type can manage products
     * 
     * Determines whether this user type can create, update, and delete
     * products in the catalog.
     * 
     * @return bool True if user can manage products
     */
    public function canManageProducts(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if user type can manage users
     * 
     * Determines whether this user type can view, create, update,
     * and manage other users in the system.
     * 
     * @return bool True if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }
}