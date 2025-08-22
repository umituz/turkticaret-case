<?php

namespace App\Repositories\Language;

use App\Models\Language\Language;
use App\Repositories\Base\BaseRepository;

class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Language
    {
        return $this->model->where('code', $code)->first();
    }
}