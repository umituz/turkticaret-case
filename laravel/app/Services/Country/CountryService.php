<?php

namespace App\Services\Country;

use App\Repositories\Country\CountryRepositoryInterface;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Collection;

class CountryService
{
    public function __construct(protected CountryRepositoryInterface $countryRepository) {}

    public function getAllCountries(): Collection
    {
        return $this->countryRepository->all();
    }

    public function createCountry(array $data): Country
    {
        return $this->countryRepository->create($data);
    }

    public function updateCountry(string $uuid, array $data): Country
    {
        return $this->countryRepository->updateByUuid($uuid, $data);
    }

    public function deleteCountry(string $uuid): bool
    {
        return $this->countryRepository->deleteByUuid($uuid);
    }

}
