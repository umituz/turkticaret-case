<?php

namespace App\Repositories\Currency;

use App\Models\Currency\Currency;
use App\Repositories\Base\BaseRepository;

class CurrencyRepository extends BaseRepository implements CurrencyRepositoryInterface
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}