<?php

namespace App\Services\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Helpers\MoneyHelper;
use App\Helpers\Order\OrderHelper;
use App\Models\Order\Order;

/**
 * Service for preparing order email data and handling email-specific logic.
 *
 * This service follows SOLID principles by separating email data preparation
 * concerns from business logic. It provides formatted, ready-to-use data
 * for order-related email templates, ensuring DRY principles.
 *
 * @package App\Services\Order
 */
class OrderMailService
{
    /**
     * Prepare all data needed for order confirmation email template.
     *
     * @param Order $order The order to prepare confirmation email data for
     * @return array Formatted email data ready for template consumption
     */
    public function prepareOrderConfirmationData(Order $order): array
    {
        return [
            'order_number' => OrderHelper::getOrderNumber($order),
            'order_title' => OrderHelper::getOrderTitle($order),
            'status_label' => OrderHelper::getStatusLabel($order),
            'total_amount_formatted' => MoneyHelper::getAmountInfo($order->total_amount)['formatted'],
            'order_date_formatted' => $order->created_at->format('M d, Y \a\t h:i A'),
            'estimated_delivery_formatted' => $order->created_at->addDays(3)->format('M d, Y'),
            'order_items_data' => $this->prepareOrderItemsData($order),
        ];
    }

    /**
     * Prepare all data needed for order status update email template.
     *
     * @param Order $order The order to prepare status update email data for
     * @param OrderStatusEnum $newStatus The new order status enum
     * @return array Formatted email data ready for template consumption
     */
    public function prepareOrderStatusUpdateData(Order $order, OrderStatusEnum $newStatus): array
    {
        return [
            'order_number' => $order->order_number,
            'total_amount_formatted' => MoneyHelper::getAmountInfo($order->total_amount)['formatted'],
            'order_date_formatted' => $order->created_at->format('M d, Y \a\t h:i A'),
            'order_items_display' => $this->prepareOrderItemsForStatusUpdate($order),
            'status_dates' => $this->prepareStatusDates($order, $newStatus),
        ];
    }

    /**
     * Prepare formatted order items data for email display.
     *
     * @param Order $order The order containing items to format
     * @return array Array of formatted order items with prices
     */
    private function prepareOrderItemsData(Order $order): array
    {
        return $order->orderItems->map(function ($item) {
            return [
                'label' => $item->product_name . ' (x' . $item->quantity . ')',
                'value' => MoneyHelper::getAmountInfo($item->total_price)['formatted'],
            ];
        })->toArray();
    }

    /**
     * Prepare order items for status update email display.
     *
     * @param Order $order The order containing items to format
     * @return array Array of formatted order items for status updates
     */
    private function prepareOrderItemsForStatusUpdate(Order $order): array
    {
        return $order->orderItems->map(function ($item) {
            return [
                'label' => $item->product_name,
                'value' => $item->quantity . 'x ' . MoneyHelper::getAmountInfo($item->unit_price)['formatted'],
            ];
        })->toArray();
    }

    /**
     * Prepare status-specific date information for email display.
     *
     * @param Order $order The order to extract dates from
     * @param OrderStatusEnum $newStatus The new status enum to check for relevant dates
     * @return array Array of formatted status dates
     */
    private function prepareStatusDates(Order $order, OrderStatusEnum $newStatus): array
    {
        $statusDates = [];

        if ($order->shipped_at && $newStatus === OrderStatusEnum::SHIPPED) {
            $statusDates[] = [
                'label' => 'Shipped Date',
                'value' => $order->shipped_at->format('M d, Y \a\t h:i A'),
            ];
        }

        if ($order->delivered_at && $newStatus === OrderStatusEnum::DELIVERED) {
            $statusDates[] = [
                'label' => 'Delivered Date',
                'value' => $order->delivered_at->format('M d, Y \a\t h:i A'),
            ];
        }

        return $statusDates;
    }
}
