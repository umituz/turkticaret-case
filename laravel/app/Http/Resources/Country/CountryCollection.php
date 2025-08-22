<?php

namespace App\Http\Resources\Country;

use App\Http\Resources\Base\BaseCollection;

class CountryCollection extends BaseCollection
{
    public $collects = CountryResource::class;
}
