<?php

namespace App\Repositories\Country;

use App\Models\Country\Country;

/**
 * Contract for Country repository implementations.
 * 
 * Defines the required methods for Country data access layer operations
 * including country lookup by code, geographical data management,
 * and location-based functionality support.
 *
 * @package App\Repositories\Country
 */
interface CountryRepositoryInterface
{
    /**
     * Find a country by its country code.
     *
     * @param string $code The country code to search for (e.g., 'US', 'TR')
     * @return Country|null The country instance if found, null otherwise
     */
    public function findByCode(string $code): ?Country;
}
