<?php

namespace App\Services\Country;

use App\Repositories\Country\CountryRepositoryInterface;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Collection;

/**
 * Country Service for country management operations.
 *
 * Handles CRUD operations for countries including creation, updating,
 * deletion, and retrieval of country information for address and
 * shipping purposes in the e-commerce system.
 *
 * @package App\Services\Country
 */
class CountryService
{
    /**
     * Create a new CountryService instance.
     *
     * @param CountryRepositoryInterface $countryRepository The country repository for data operations
     */
    public function __construct(protected CountryRepositoryInterface $countryRepository) {}

    /**
     * Get all available countries.
     *
     * @return Collection Collection of all countries in the system
     */
    public function getAllCountries(): Collection
    {
        return $this->countryRepository->all();
    }

    /**
     * Create a new country.
     *
     * @param array $data Country data including name, code, and other attributes
     * @return Country The newly created country instance
     */
    public function createCountry(array $data): Country
    {
        return $this->countryRepository->create($data);
    }

    /**
     * Update an existing country.
     *
     * @param string $uuid The UUID of the country to update
     * @param array $data Updated country data
     * @return Country The updated country instance
     * @throws \Exception
     */
    public function updateCountry(string $uuid, array $data): Country
    {
        return $this->countryRepository->updateByUuid($uuid, $data);
    }

    /**
     * Delete a country.
     *
     * @param string $uuid The UUID of the country to delete
     * @return bool True if deletion was successful, false otherwise
     */
    public function deleteCountry(string $uuid): bool
    {
        return $this->countryRepository->deleteByUuid($uuid);
    }

    /**
     * Find a country by its country code.
     *
     * @param string $code The country code to search for (e.g., 'US', 'TR')
     * @return Country The country instance matching the code
     * @throws \InvalidArgumentException When country code is invalid or not found
     */
    public function findByCode(string $code): Country
    {
        $country = $this->countryRepository->findByCode($code);

        if (!$country) {
            throw new \InvalidArgumentException('Invalid country code provided');
        }

        return $country;
    }

}
