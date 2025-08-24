<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Sort filter for applying ordering to query results.
 * 
 * Handles dynamic sorting of query results based on specified field and direction.
 * Supports ascending and descending order with field validation against a whitelist
 * of sortable fields defined in the model.
 *
 * @package App\Filters\Common
 */
class SortFilter extends AbstractFilter
{
    /**
     * Apply sorting filter to the query builder.
     * 
     * Validates and applies ordering based on the orderBy and order parameters.
     * Uses the model's sortableFields property to ensure only allowed fields
     * can be used for sorting, with secure column name escaping to prevent
     * SQL injection attacks.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder with applied sorting
     */
    public function handle(Builder $builder, Closure $next): Builder
    {
        $orderBy = $this->data[FilterEnums::PARAM_ORDER_BY] ?? FilterEnums::DEFAULT_ORDER_BY;
        $order = $this->data[FilterEnums::PARAM_ORDER] ?? FilterEnums::DEFAULT_ORDER;
        
        $order = strtolower($order);
        if (!in_array($order, [FilterEnums::ORDER_ASC, FilterEnums::ORDER_DESC])) {
            $order = FilterEnums::DEFAULT_ORDER;
        }
        
        $model = $builder->getModel();
        $sortableFields = $model->sortableFields ?? ['created_at', 'updated_at', 'name'];
        
        if (in_array($orderBy, $sortableFields)) {
            // Secure column name escaping to prevent SQL injection
            $escapedColumn = DB::getQueryGrammar()->wrap($orderBy);
            $builder->orderBy(DB::raw($escapedColumn), $order);
        } else {
            $builder->orderBy(FilterEnums::DEFAULT_ORDER_BY, FilterEnums::DEFAULT_ORDER);
        }
        
        return $next($builder);
    }
}