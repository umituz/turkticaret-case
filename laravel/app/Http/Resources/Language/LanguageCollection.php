<?php

namespace App\Http\Resources\Language;

use App\Http\Resources\Base\BaseCollection;

/**
 * API Resource Collection for transforming Language data.
 * 
 * Handles the transformation of multiple Language model instances into standardized
 * JSON API response collections. Provides consistent pagination, filtering,
 * and collection metadata for language listings and localization support.
 *
 * @package App\Http\Resources\Language
 */
class LanguageCollection extends BaseCollection
{
    public $collects = LanguageResource::class;
}
