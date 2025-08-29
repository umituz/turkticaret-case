<?php

namespace App\Filters\Base;

/**
 * Filter constants and default values for query filtering operations.
 *
 * This class centralizes all filter-related constants used throughout the
 * application's filtering system. It defines parameter names, default values,
 * and common operators used in query filtering and pagination.
 *
 * @package App\Filters\Base
 */
class FilterEnums
{
    /**
     * Request parameter name for pagination page number.
     */
    public const PARAM_PAGE = 'page';

    /**
     * Request parameter name for items per page in pagination.
     */
    public const PARAM_PER_PAGE = 'per_page';

    /**
     * Request parameter name for search query string.
     */
    public const PARAM_SEARCH = 'search';

    /**
     * Request parameter name for specifying which field to search by.
     */
    public const PARAM_SEARCH_BY = 'searchBy';

    /**
     * Request parameter name for specifying the field to order results by.
     */
    public const PARAM_ORDER_BY = 'orderBy';

    /**
     * Request parameter name for specifying the sort direction.
     */
    public const PARAM_ORDER = 'order';

    /**
     * Request parameter name for date range filter start date.
     */
    public const PARAM_START_DATE = 'start_date';

    /**
     * Request parameter name for date range filter end date.
     */
    public const PARAM_END_DATE = 'end_date';

    /**
     * Default number of items per page for pagination.
     */
    public const DEFAULT_PER_PAGE = 20;

    /**
     * Maximum number of items allowed per page for pagination.
     */
    public const MAX_PER_PAGE = 100;

    /**
     * Default field to order results by.
     */
    public const DEFAULT_ORDER_BY = 'created_at';

    /**
     * Default sort direction for query results.
     */
    public const DEFAULT_ORDER = 'desc';

    /**
     * Ascending sort order constant.
     */
    public const ORDER_ASC = 'asc';

    /**
     * Descending sort order constant.
     */
    public const ORDER_DESC = 'desc';

    /**
     * PostgreSQL case-insensitive LIKE operator for search operations.
     */
    public const SEARCH_OPERATOR = 'ILIKE';

    /**
     * Wildcard character used in search patterns.
     */
    public const SEARCH_WILDCARD = '%';
}
