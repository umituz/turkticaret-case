<?php

namespace App\DTOs\Order;

use App\Models\Order\Order;

/**
 * Data Transfer Object for order status change operations.
 * 
 * Encapsulates all data required for handling order status changes including
 * order instance, old and new status values, and user context. Provides a
 * structured way to pass status change data between layers.
 *
 * @package App\DTOs\Order
 */
class OrderStatusChangeDTO
{
    public function __construct(
        public readonly Order $order,
        public readonly mixed $oldStatus,
        public readonly string $newStatus,
        public readonly ?string $changedByUuid = null,
        public readonly ?string $notes = null
    ) {}

    /**
     * Create DTO for order creation status history.
     *
     * @param Order $order The newly created order
     * @param string|null $changedByUuid UUID of user creating the order
     * @return static
     */
    public static function forOrderCreated(Order $order, ?string $changedByUuid = null): static
    {
        return new static(
            order: $order,
            oldStatus: null,
            newStatus: $order->status->value,
            changedByUuid: $changedByUuid ?? auth()->user()?->uuid,
            notes: 'Order created'
        );
    }

    /**
     * Create DTO for order status update.
     *
     * @param Order $order The order being updated
     * @param mixed $oldStatus The previous status
     * @param string $newStatus The new status value
     * @param string|null $changedByUuid UUID of user making the change
     * @return static
     */
    public static function forStatusUpdate(
        Order $order, 
        mixed $oldStatus, 
        string $newStatus, 
        ?string $changedByUuid = null
    ): static {
        return new static(
            order: $order,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            changedByUuid: $changedByUuid ?? auth()->user()?->uuid,
            notes: 'Status updated'
        );
    }

    /**
     * Get the old status as string value.
     *
     * @return string|null
     */
    public function getOldStatusValue(): ?string
    {
        if ($this->oldStatus === null) {
            return null;
        }

        return is_object($this->oldStatus) && method_exists($this->oldStatus, 'value')
            ? $this->oldStatus->value
            : (string) $this->oldStatus;
    }

    /**
     * Convert DTO to array for repository operations.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'order_uuid' => $this->order->uuid,
            'old_status' => $this->getOldStatusValue(),
            'new_status' => (string) $this->newStatus,
            'changed_by_uuid' => $this->changedByUuid,
            'notes' => $this->notes,
        ];
    }
}