<?php

namespace App\Models\Shipping;

use App\Models\Base\BaseUuidModel;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDeliveryTimeAttribute(): string
    {
        if ($this->min_delivery_days === $this->max_delivery_days) {
            return "{$this->min_delivery_days} business day" . ($this->min_delivery_days > 1 ? 's' : '');
        }

        return "{$this->min_delivery_days}-{$this->max_delivery_days} business days";
    }
}