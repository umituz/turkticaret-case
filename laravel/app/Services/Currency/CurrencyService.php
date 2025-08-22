<?php

namespace App\Services\Currency;

use App\Repositories\Currency\CurrencyRepositoryInterface;
use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    public function __construct(protected CurrencyRepositoryInterface $currencyRepository) {}

    public function getAllCurrencies(): Collection
    {
        return $this->currencyRepository->all();
    }

    public function createCurrency(array $data): Currency
    {
        return $this->currencyRepository->create($data);
    }

    public function updateCurrency(string $uuid, array $data): Currency
    {
        return $this->currencyRepository->updateByUuid($uuid, $data);
    }

    public function deleteCurrency(string $uuid): bool
    {
        return $this->currencyRepository->deleteByUuid($uuid);
    }
}