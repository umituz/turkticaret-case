<?php

namespace App\Enums\Order;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public static function getAvailableStatuses(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($newStatus, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($newStatus, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => in_array($newStatus, [self::DELIVERED]),
            self::DELIVERED => false,
            self::CANCELLED => false,
        };
    }
}