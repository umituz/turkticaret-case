<?php

namespace App\Repositories\Country;

use App\Models\Country\Country;

interface CountryRepositoryInterface
{
    public function findByCode(string $code): ?Country;
}
