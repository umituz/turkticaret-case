<?php

namespace App\Enums\Order;

/**
 * Order Status Enumeration for managing order lifecycle states.
 * 
 * Defines all possible order states in the e-commerce system with
 * transition validation and label formatting. Ensures consistent
 * order status handling throughout the application.
 *
 * @package App\Enums\Order
 */
enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Get all available order status values.
     *
     * @return array Array of all order status string values
     */
    public static function getAvailableStatuses(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label for the order status.
     *
     * @return string Formatted label for display purposes
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    /**
     * Check if the current status can transition to the specified new status.
     *
     * @param OrderStatusEnum $newStatus The target status to transition to
     * @return bool True if the transition is allowed, false otherwise
     */
    public function canTransitionTo(OrderStatusEnum $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($newStatus, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($newStatus, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => in_array($newStatus, [self::DELIVERED]),
            self::DELIVERED => in_array($newStatus, [self::REFUNDED]),
            self::CANCELLED => in_array($newStatus, [self::REFUNDED]),
            self::REFUNDED => false,
        };
    }
}