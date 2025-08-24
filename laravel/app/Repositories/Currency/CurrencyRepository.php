<?php

namespace App\Repositories\Currency;

use App\Models\Currency\Currency;
use App\Repositories\Base\BaseRepository;

/**
 * Currency repository for handling currency-related database operations.
 * 
 * This repository provides methods for managing currency data including
 * standard CRUD operations and currency-specific queries.
 *
 * @package App\Repositories\Currency
 */
class CurrencyRepository extends BaseRepository implements CurrencyRepositoryInterface
{
    /**
     * Create a new Currency repository instance.
     *
     * @param Currency $model The Currency model instance
     */
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}