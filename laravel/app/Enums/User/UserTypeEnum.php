<?php

namespace App\Enums\User;

enum UserTypeEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public static function getAvailableTypes(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'Regular User',
        };
    }

    public function getEmail(): string
    {
        return match ($this) {
            self::ADMIN => 'admin@test.com',
            self::USER => 'user@test.com',
        };
    }

    public function hasAdminAccess(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageOrders(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageProducts(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }
}