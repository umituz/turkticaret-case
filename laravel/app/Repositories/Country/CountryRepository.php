<?php

namespace App\Repositories\Country;

use App\Models\Country\Country;
use App\Repositories\Base\BaseRepository;

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Country
    {
        return $this->model->where('code', $code)->first();
    }
}
