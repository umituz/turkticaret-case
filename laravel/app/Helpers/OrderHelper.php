<?php

namespace App\Helpers;

use App\Enums\Order\OrderEnum;
use App\Models\Order\Order;

/**
 * Order Helper Class
 *
 * Provides utility functions for order-related operations including
 * order number formatting, display helpers, and common order utilities.
 *
 * @package App\Helpers\Order
 */
class OrderHelper
{
    /**
     * Generate a readable order number from UUID.
     *
     * Takes the first 8 characters of the order UUID and converts
     * to uppercase for better readability and consistency.
     *
     * @param string $uuid The order UUID
     * @return string Formatted order number (e.g., 'A1B2C3D4')
     */
    public static function formatOrderNumber(string $uuid): string
    {
        return strtoupper(substr($uuid, 0, OrderEnum::getOrderNumberUuidLength()));
    }

    /**
     * Get formatted order number from Order model.
     *
     * @param Order $order The order instance
     * @return string Formatted order number
     */
    public static function getOrderNumber(Order $order): string
    {
        return self::formatOrderNumber($order->uuid);
    }

    /**
     * Generate order display title with number.
     *
     * @param Order $order The order instance
     * @return string Order title (e.g., 'Order #A1B2C3D4')
     */
    public static function getOrderTitle(Order $order): string
    {
        return 'Order #' . self::getOrderNumber($order);
    }

    /**
     * Format order total amount for display using MoneyHelper standards.
     *
     * @param int $amount Amount in minor units (cents)
     * @param string $currency Currency symbol (default: '₺')
     * @return string Formatted amount (e.g., '₺99.99')
     * @deprecated Use MoneyHelper::formatAmount() instead
     */
    public static function formatAmount(int $amount, string $currency = '₺'): string
    {
        return MoneyHelper::formatAmount($amount, $currency);
    }

    /**
     * Get order status display label.
     *
     * @param Order $order The order instance
     * @return string Human-readable status label
     */
    public static function getStatusLabel(Order $order): string
    {
        return $order->status?->getLabel() ?? ucfirst($order->status);
    }
}
