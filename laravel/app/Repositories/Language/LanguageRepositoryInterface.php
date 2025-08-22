<?php

namespace App\Repositories\Language;

use App\Models\Language\Language;

interface LanguageRepositoryInterface
{
    public function findByCode(string $code): ?Language;
}
