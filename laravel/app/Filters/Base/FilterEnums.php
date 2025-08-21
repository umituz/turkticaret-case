<?php

namespace App\Filters\Base;

class FilterEnums
{
    public const PARAM_PAGE = 'page';
    public const PARAM_PER_PAGE = 'per_page';
    public const PARAM_SEARCH = 'search';
    public const PARAM_SEARCH_BY = 'searchBy';
    public const PARAM_ORDER_BY = 'orderBy';
    public const PARAM_ORDER = 'order';
    public const PARAM_START_DATE = 'start_date';
    public const PARAM_END_DATE = 'end_date';
    
    public const DEFAULT_PER_PAGE = 15;
    public const MAX_PER_PAGE = 100;
    public const DEFAULT_ORDER_BY = 'created_at';
    public const DEFAULT_ORDER = 'desc';
    
    public const ORDER_ASC = 'asc';
    public const ORDER_DESC = 'desc';
    
    public const SEARCH_OPERATOR = 'ILIKE';
    public const SEARCH_WILDCARD = '%';
}