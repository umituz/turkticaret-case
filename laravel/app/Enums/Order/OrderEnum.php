<?php

namespace App\Enums\Order;

/**
 * General Order Enumeration for order-related constants and configurations.
 * 
 * Contains various order-related constants such as delivery days,
 * timeouts, limits, and other order-specific configuration values
 * that are used throughout the order management system.
 *
 * @package App\Enums\Order
 */
enum OrderEnum: int
{
    case DEFAULT_ESTIMATED_DELIVERY_DAYS = 3;
    case MAX_ORDER_ITEMS = 50;
    case ORDER_NUMBER_UUID_LENGTH = 8;
    case MINIMUM_ORDER_AMOUNT_CENTS = 1000; // 10.00 in currency

    /**
     * Get the estimated delivery days for new orders.
     *
     * @return int Number of days for estimated delivery
     */
    public static function getEstimatedDeliveryDays(): int
    {
        return self::DEFAULT_ESTIMATED_DELIVERY_DAYS->value;
    }

    /**
     * Get the maximum number of items allowed in an order.
     *
     * @return int Maximum order items count
     */
    public static function getMaxOrderItems(): int
    {
        return self::MAX_ORDER_ITEMS->value;
    }

    /**
     * Get the UUID length for order number generation.
     *
     * @return int UUID length for order numbers
     */
    public static function getOrderNumberUuidLength(): int
    {
        return self::ORDER_NUMBER_UUID_LENGTH->value;
    }

    /**
     * Get the minimum order amount in cents.
     *
     * @return int Minimum order amount in cents
     */
    public static function getMinimumOrderAmountCents(): int
    {
        return self::MINIMUM_ORDER_AMOUNT_CENTS->value;
    }
}