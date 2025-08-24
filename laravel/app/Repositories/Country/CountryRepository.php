<?php

namespace App\Repositories\Country;

use App\Models\Country\Country;
use App\Repositories\Base\BaseRepository;

/**
 * Country repository for handling country-related database operations.
 * 
 * This repository provides methods for managing country data including
 * finding countries by code and other country-specific queries.
 *
 * @package App\Repositories\Country
 */
class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    /**
     * Create a new Country repository instance.
     *
     * @param Country $model The Country model instance
     */
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a country by its code.
     *
     * @param string $code The country code to search for
     * @return Country|null The country instance or null if not found
     */
    public function findByCode(string $code): ?Country
    {
        return $this->model->where('code', $code)->first();
    }
}
