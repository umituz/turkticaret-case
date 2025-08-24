<?php

namespace App\Http\Resources\Currency;

use App\Http\Resources\Base\BaseCollection;

/**
 * API Collection Resource for transforming Currency data.
 * 
 * Handles the transformation of Currency model collections into standardized
 * JSON API responses. Automatically collects CurrencyResource instances
 * and applies pagination, filtering and formatting for financial operations.
 *
 * @package App\Http\Resources\Currency
 */
class CurrencyCollection extends BaseCollection
{
    public $collects = CurrencyResource::class;
}
