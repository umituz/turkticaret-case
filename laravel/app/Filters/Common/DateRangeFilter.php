<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Date range filter for filtering records by creation date.
 * 
 * Applies date range filtering to query results based on start_date and end_date
 * parameters. Filters against the created_at column by default, supporting
 * both inclusive start and end date filtering.
 *
 * @package App\Filters\Common
 */
class DateRangeFilter extends AbstractFilter
{
    /**
     * Apply date range filter to the query builder.
     * 
     * Filters records based on start_date and end_date parameters against
     * the created_at column. Both parameters are optional and can be used
     * independently to create open-ended date ranges.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder with applied date filtering
     */
    public function handle(Builder $builder, Closure $next): Builder
    {
        $startDate = $this->data[FilterEnums::PARAM_START_DATE] ?? null;
        $endDate = $this->data[FilterEnums::PARAM_END_DATE] ?? null;
        
        if ($startDate) {
            $builder->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $builder->whereDate('created_at', '<=', $endDate);
        }
        
        return $next($builder);
    }
}