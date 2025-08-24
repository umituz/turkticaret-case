<?php

namespace App\Enums\Authority;

/**
 * Permission enumeration for role-based access control
 * 
 * Defines all available permissions in the application for users, products,
 * categories, orders, and cart management. Used for implementing role-based
 * access control (RBAC) throughout the application.
 * 
 * @package App\Enums\Authority
 */
enum PermissionEnum: string
{
    // User Management
    case USER_READ = 'user.read';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';

    // Product Management
    case PRODUCT_READ = 'product.read';
    case PRODUCT_CREATE = 'product.create';
    case PRODUCT_UPDATE = 'product.update';
    case PRODUCT_DELETE = 'product.delete';

    // Category Management
    case CATEGORY_READ = 'category.read';
    case CATEGORY_CREATE = 'category.create';
    case CATEGORY_UPDATE = 'category.update';
    case CATEGORY_DELETE = 'category.delete';

    // Order Management
    case ORDER_READ = 'order.read';
    case ORDER_CREATE = 'order.create';
    case ORDER_UPDATE = 'order.update';
    case ORDER_DELETE = 'order.delete';
    case ORDER_MANAGE = 'order.manage';

    // Cart Management
    case CART_READ = 'cart.read';
    case CART_UPDATE = 'cart.update';

    /**
     * Get human-readable description for the permission
     * 
     * @return string Permission description for display purposes
     */
    public function description(): string
    {
        return match($this) {
            self::USER_READ => 'View Users',
            self::USER_CREATE => 'Create Users',
            self::USER_UPDATE => 'Update Users',
            self::USER_DELETE => 'Delete Users',

            self::PRODUCT_READ => 'View Products',
            self::PRODUCT_CREATE => 'Create Products',
            self::PRODUCT_UPDATE => 'Update Products',
            self::PRODUCT_DELETE => 'Delete Products',

            self::CATEGORY_READ => 'View Categories',
            self::CATEGORY_CREATE => 'Create Categories',
            self::CATEGORY_UPDATE => 'Update Categories',
            self::CATEGORY_DELETE => 'Delete Categories',

            self::ORDER_READ => 'View Orders',
            self::ORDER_CREATE => 'Create Orders',
            self::ORDER_UPDATE => 'Update Orders',
            self::ORDER_DELETE => 'Delete Orders',
            self::ORDER_MANAGE => 'Manage All Orders',

            self::CART_READ => 'View Cart',
            self::CART_UPDATE => 'Update Cart',
        };
    }

    /**
     * Get all permission values as array
     * 
     * @return array Array of all permission string values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all admin permissions
     * 
     * Returns all permissions available to admin users including
     * create, read, update, and delete operations for all resources.
     * 
     * @return array Array of admin permission values
     */
    public static function getAdminPermissions(): array
    {
        return [
            self::USER_READ->value,
            self::USER_CREATE->value,
            self::USER_UPDATE->value,
            self::USER_DELETE->value,
            self::PRODUCT_READ->value,
            self::PRODUCT_CREATE->value,
            self::PRODUCT_UPDATE->value,
            self::PRODUCT_DELETE->value,
            self::CATEGORY_READ->value,
            self::CATEGORY_CREATE->value,
            self::CATEGORY_UPDATE->value,
            self::CATEGORY_DELETE->value,
            self::ORDER_READ->value,
            self::ORDER_CREATE->value,
            self::ORDER_UPDATE->value,
            self::ORDER_DELETE->value,
            self::ORDER_MANAGE->value,
            self::CART_READ->value,
            self::CART_UPDATE->value,
        ];
    }

    /**
     * Get regular user permissions
     * 
     * Returns limited set of permissions for regular users, mainly
     * read access and basic operations on their own data.
     * 
     * @return array Array of regular user permission values
     */
    public static function getRegularPermissions(): array
    {
        return [
            self::PRODUCT_READ->value,
            self::CATEGORY_READ->value,
            self::ORDER_READ->value,
            self::ORDER_CREATE->value,
            self::CART_READ->value,
            self::CART_UPDATE->value,
        ];
    }
}
