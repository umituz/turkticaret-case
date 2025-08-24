<?php

namespace App\Http\Resources\Country;

use App\Http\Resources\Base\BaseCollection;

/**
 * API Collection Resource for transforming Country data.
 * 
 * Handles the transformation of Country model collections into standardized
 * JSON API responses. Automatically collects CountryResource instances
 * and applies pagination, filtering and formatting for geographical operations.
 *
 * @package App\Http\Resources\Country
 */
class CountryCollection extends BaseCollection
{
    public $collects = CountryResource::class;
}
