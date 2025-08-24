<?php

namespace App\Models\Shipping;

use App\Models\Base\BaseUuidModel;

/**
 * ShippingMethod Model for e-commerce shipping options.
 * 
 * Manages shipping method configurations including pricing, delivery times,
 * and availability status. Provides shipping options for order fulfillment
 * with estimated delivery timeframes and cost calculations.
 *
 * @property string $uuid Shipping method unique identifier
 * @property string $name Shipping method name (e.g., 'Standard Shipping')
 * @property string|null $description Detailed description of the shipping method
 * @property float $price Shipping cost in the base currency
 * @property int $min_delivery_days Minimum estimated delivery days
 * @property int $max_delivery_days Maximum estimated delivery days
 * @property bool $is_active Whether the shipping method is available
 * @property int $sort_order Display order for shipping method listing
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Shipping
 */
class ShippingMethod extends BaseUuidModel
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'min_delivery_days',
        'max_delivery_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'min_delivery_days' => 'integer',
            'max_delivery_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope to filter only active shipping methods.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get a formatted delivery time string.
     *
     * @return string Human-readable delivery time estimate
     */
    public function getDeliveryTimeAttribute(): string
    {
        if ($this->min_delivery_days === $this->max_delivery_days) {
            return "{$this->min_delivery_days} business day" . ($this->min_delivery_days > 1 ? 's' : '');
        }

        return "{$this->min_delivery_days}-{$this->max_delivery_days} business days";
    }
}