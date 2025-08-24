<?php

namespace App\Enums;

/**
 * API configuration enumerations for application constants.
 * 
 * Provides centralized constant values used throughout the API including
 * default pagination settings and other API-wide configuration values.
 *
 * @package App\Enums
 */
enum ApiEnums: int
{
    /**
     * Default number of items per page for API pagination.
     */
    case DEFAULT_PAGINATION = 20;
}
