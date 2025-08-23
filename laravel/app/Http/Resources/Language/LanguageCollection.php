<?php

namespace App\Http\Resources\Language;

use App\Http\Resources\Base\BaseCollection;

class LanguageCollection extends BaseCollection
{
    public $collects = LanguageResource::class;
}
