<?php

declare(strict_types=1);

namespace App\Traits;

use App\Helpers\MoneyHelper;

/**
 * HasMoneyAttributes trait for money value handling and formatting.
 *
 * Provides automatic money formatting functionality for models with monetary
 * attributes. Enables magic attribute access for formatted money values and
 * detailed money information including currency symbols and decimal places.
 *
 * @package App\Traits
 */
trait HasMoneyAttributes
{
    /**
     * Get the list of attributes that should be treated as money values.
     *
     * Implementing models must define which attributes contain money values
     * that should be formatted using the money formatting system.
     *
     * @return array Array of attribute names that contain money values
     */
    abstract protected function getMoneyAttributes(): array;

     /**
     * Boot method to register money mutators for money attributes.
     *
     * @return void
     */
    protected static function bootHasMoneyAttributes(): void
    {
        static::saving(function ($model) {
            foreach ($model->getMoneyAttributes() as $attribute) {
                if (isset($model->attributes[$attribute]) && is_numeric($model->attributes[$attribute])) {
                    $model->attributes[$attribute] = (int) round($model->attributes[$attribute] * 100);
                }
            }
        });
    }

    /**
     * Magic getter for attribute access with money formatting support.
     *
     * Intercepts attribute access to provide automatic formatting for money
     * attributes. Supports '_formatted' and '_info' suffixes for enhanced
     * money value presentation.
     *
     * @param string $key The attribute key being accessed
     * @return mixed The attribute value, formatted if it's a money attribute
     */
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

    /**
     * Check if the requested key is a formatted money attribute.
     *
     * Determines if the attribute key ends with '_formatted' and corresponds
     * to a defined money attribute in the model.
     *
     * @param string $key The attribute key to check
     * @return bool True if it's a formatted money attribute, false otherwise
     */
    private function isMoneyFormattedAttribute(string $key): bool
    {
        $suffix = '_formatted';
        if (!str_ends_with($key, $suffix)) {
            return false;
        }

        $baseAttribute = substr($key, 0, -strlen($suffix));
        return in_array($baseAttribute, $this->getMoneyAttributes());
    }

    /**
     * Check if the requested key is a money info attribute.
     *
     * Determines if the attribute key ends with '_info' and corresponds
     * to a defined money attribute in the model.
     *
     * @param string $key The attribute key to check
     * @return bool True if it's a money info attribute, false otherwise
     */
    private function isMoneyInfoAttribute(string $key): bool
    {
        $suffix = '_info';
        if (!str_ends_with($key, $suffix)) {
            return false;
        }

        $baseAttribute = substr($key, 0, -strlen($suffix));
        return in_array($baseAttribute, $this->getMoneyAttributes());
    }

    /**
     * Get the formatted money value for a money attribute.
     *
     * Retrieves the raw money value and formats it for display using
     * the appropriate currency symbol and formatting rules.
     *
     * @param string $key The formatted money attribute key
     * @return string The formatted money value (e.g., '$19.99')
     */
    private function getFormattedMoneyAttribute(string $key): string
    {
        $baseAttribute = substr($key, 0, -strlen('_formatted'));
        $value = $this->attributes[$baseAttribute] ?? 0;

        return $this->formatMoneyValue($value);
    }

    /**
     * Get detailed money information for a money attribute.
     *
     * Returns comprehensive money information including formatted value,
     * currency symbol, and decimal breakdown.
     *
     * @param string $key The money info attribute key
     * @return array Detailed money information array
     */
    private function getMoneyInfoAttribute(string $key): array
    {
        $baseAttribute = substr($key, 0, -strlen('_info'));
        $value = $this->attributes[$baseAttribute] ?? 0;

        return MoneyHelper::getAmountInfo($value, $this->getCurrencySymbol());
    }

    /**
     * Format a money value using the appropriate currency symbol.
     *
     * Converts raw integer money values to formatted currency strings
     * using the model's associated currency symbol.
     *
     * @param int $value The raw money value in cents
     * @return string The formatted money string
     */
    private function formatMoneyValue(int $value): string
    {
        return MoneyHelper::formatAmount($value, $this->getCurrencySymbol());
    }

    /**
     * Get the appropriate currency symbol for this model.
     *
     * Attempts to retrieve the currency symbol from related models (user's
     * country currency or model's country currency), falling back to '$'.
     *
     * @return string The currency symbol to use for formatting
     */
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
