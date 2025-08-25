<?php

namespace App\Services\Order;

use App\Models\Order\Order;
use App\Enums\Order\OrderStatusEnum;
use Illuminate\Support\Collection;

/**
 * Order Status History Service for building order status timeline.
 *
 * Handles the creation of order status history with proper enum usage
 * and follows SOLID principles for maintainable and extensible code.
 *
 * @package App\Services\Order
 */
class OrderStatusHistoryService
{
    /**
     * Build complete order status history.
     *
     * @param Order $order The order to build history for
     * @return array Complete order status history
     */
    public function buildHistory(Order $order): array
    {
        return [
            'order_uuid' => $order->uuid,
            'current_status' => $order->status->value,
            'history' => $this->buildStatusHistory($order)
        ];
    }

    /**
     * Build order status history timeline.
     *
     * @param Order $order The order to build timeline for
     * @return array Array of status history entries
     */
    private function buildStatusHistory(Order $order): array
    {
        $history = collect();

        $this->addOrderPlacedEntry($history, $order);
        $this->addProcessingEntryIfApplicable($history, $order);
        $this->addShippedEntryIfExists($history, $order);
        $this->addDeliveredEntryIfExists($history, $order);
        $this->addCurrentStatusIfDifferent($history, $order);

        return $history->toArray();
    }

    /**
     * Add order placed entry to history.
     *
     * @param Collection $history The history collection
     * @param Order $order The order instance
     * @return void
     */
    private function addOrderPlacedEntry(Collection $history, Order $order): void
    {
        $history->push($this->createHistoryEntry(
            OrderStatusEnum::PENDING,
            $order->created_at,
            'Order placed'
        ));
    }

    /**
     * Add processing entry if conditions are met.
     *
     * @param Collection $history The history collection
     * @param Order $order The order instance
     * @return void
     */
    private function addProcessingEntryIfApplicable(Collection $history, Order $order): void
    {
        if ($this->shouldAddProcessingStatus($order)) {
            $history->push($this->createHistoryEntry(
                OrderStatusEnum::PROCESSING,
                $order->updated_at,
                'Order confirmed and processing'
            ));
        }
    }

    /**
     * Add shipped entry if shipping date exists.
     *
     * @param Collection $history The history collection
     * @param Order $order The order instance
     * @return void
     */
    private function addShippedEntryIfExists(Collection $history, Order $order): void
    {
        if ($order->shipped_at) {
            $history->push($this->createHistoryEntry(
                OrderStatusEnum::SHIPPED,
                $order->shipped_at,
                'Order shipped'
            ));
        }
    }

    /**
     * Add delivered entry if delivery date exists.
     *
     * @param Collection $history The history collection
     * @param Order $order The order instance
     * @return void
     */
    private function addDeliveredEntryIfExists(Collection $history, Order $order): void
    {
        if ($order->delivered_at) {
            $history->push($this->createHistoryEntry(
                OrderStatusEnum::DELIVERED,
                $order->delivered_at,
                'Order delivered'
            ));
        }
    }

    /**
     * Add current status to history if it differs from the last entry.
     *
     * @param Collection $history The history collection
     * @param Order $order The order instance
     * @return void
     */
    private function addCurrentStatusIfDifferent(Collection $history, Order $order): void
    {
        $lastHistoryStatus = $history->last()['status'] ?? OrderStatusEnum::PENDING->value;
        
        if ($order->status->value !== $lastHistoryStatus) {
            $history->push($this->createHistoryEntry(
                $order->status,
                $order->updated_at,
                'Order status updated to ' . $order->status->getLabel()
            ));
        }
    }

    /**
     * Create a history entry with consistent structure.
     *
     * @param OrderStatusEnum $status The status enum
     * @param mixed $date The date for this status
     * @param string $description The description for this status
     * @return array
     */
    private function createHistoryEntry(OrderStatusEnum $status, $date, string $description): array
    {
        return [
            'status' => $status->value,
            'date' => $date,
            'description' => $description
        ];
    }

    /**
     * Check if processing status should be added to history.
     *
     * @param Order $order The order to check
     * @return bool
     */
    private function shouldAddProcessingStatus(Order $order): bool
    {
        return $order->status !== OrderStatusEnum::PENDING 
            && $order->updated_at > $order->created_at;
    }
}