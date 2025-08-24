<?php

namespace App\Services\Currency;

use App\Repositories\Currency\CurrencyRepositoryInterface;
use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Collection;

/**
 * Currency Service for multi-currency system operations.
 *
 * Handles CRUD operations for system currencies including creating,
 * updating, deleting, and retrieving currency configurations for
 * multi-currency support in the e-commerce system.
 *
 * @package App\Services\Currency
 */
class CurrencyService
{
    /**
     * Create a new CurrencyService instance.
     *
     * @param CurrencyRepositoryInterface $currencyRepository The currency repository for data operations
     */
    public function __construct(protected CurrencyRepositoryInterface $currencyRepository) {}

    /**
     * Get all available currencies.
     *
     * @return Collection Collection of all currencies in the system
     */
    public function getAllCurrencies(): Collection
    {
        return $this->currencyRepository->all();
    }

    /**
     * Create a new currency.
     *
     * @param array $data Currency data including code, name, symbol, and exchange rate
     * @return Currency The newly created currency instance
     * @throws \Exception
     */
    public function createCurrency(array $data): Currency
    {
        return $this->currencyRepository->create($data);
    }

    /**
     * Update an existing currency.
     *
     * @param string $uuid The UUID of the currency to update
     * @param array $data Updated currency data
     * @return Currency The updated currency instance
     * @throws \Exception
     */
    public function updateCurrency(string $uuid, array $data): Currency
    {
        return $this->currencyRepository->updateByUuid($uuid, $data);
    }

    /**
     * Delete a currency.
     *
     * @param string $uuid The UUID of the currency to delete
     * @return bool True if deletion was successful, false otherwise
     * @throws \Exception
     */
    public function deleteCurrency(string $uuid): bool
    {
        return $this->currencyRepository->deleteByUuid($uuid);
    }
}
