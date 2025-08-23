<?php

declare(strict_types=1);

namespace App\Traits;

use App\Helpers\MoneyHelper;

trait HasMoneyAttributes
{
    abstract protected function getMoneyAttributes(): array;

    public function __get($key)
    {
        if (in_array($key, $this->getMoneyAttributes())) {
            return parent::__get($key);
        }

        if ($this->isMoneyFormattedAttribute($key)) {
            return $this->getFormattedMoneyAttribute($key);
        }

        if ($this->isMoneyInfoAttribute($key)) {
            return $this->getMoneyInfoAttribute($key);
        }

        return parent::__get($key);
    }

    private function isMoneyFormattedAttribute(string $key): bool
    {
        $suffix = '_formatted';
        if (!str_ends_with($key, $suffix)) {
            return false;
        }

        $baseAttribute = substr($key, 0, -strlen($suffix));
        return in_array($baseAttribute, $this->getMoneyAttributes());
    }

    private function isMoneyInfoAttribute(string $key): bool
    {
        $suffix = '_info';
        if (!str_ends_with($key, $suffix)) {
            return false;
        }

        $baseAttribute = substr($key, 0, -strlen($suffix));
        return in_array($baseAttribute, $this->getMoneyAttributes());
    }

    private function getFormattedMoneyAttribute(string $key): string
    {
        $baseAttribute = substr($key, 0, -strlen('_formatted'));
        $value = $this->attributes[$baseAttribute] ?? 0;
        
        return $this->formatMoneyValue($value);
    }

    private function getMoneyInfoAttribute(string $key): array
    {
        $baseAttribute = substr($key, 0, -strlen('_info'));
        $value = $this->attributes[$baseAttribute] ?? 0;
        
        return MoneyHelper::getAmountInfo($value, $this->getCurrencySymbol());
    }

    private function formatMoneyValue(int $value): string
    {
        return MoneyHelper::formatAmount($value, $this->getCurrencySymbol());
    }

    private function getCurrencySymbol(): string
    {
        if ($this->relationLoaded('user') && $this->user?->relationLoaded('country') && $this->user?->country?->relationLoaded('currency')) {
            return $this->user->country->currency->symbol ?? '$';
        }

        if ($this->relationLoaded('country') && $this->country?->relationLoaded('currency')) {
            return $this->country->currency->symbol ?? '$';
        }

        return '$';
    }
}