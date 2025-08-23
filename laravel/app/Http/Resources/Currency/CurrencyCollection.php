<?php

namespace App\Http\Resources\Currency;

use App\Http\Resources\Base\BaseCollection;

class CurrencyCollection extends BaseCollection
{
    public $collects = CurrencyResource::class;
}
