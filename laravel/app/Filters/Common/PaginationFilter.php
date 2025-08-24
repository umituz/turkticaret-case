<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pagination filter for managing page and per-page parameters.
 * 
 * Handles pagination parameter validation and normalization. Sets pagination
 * data on the model instance for later use by pagination methods. Enforces
 * maximum per-page limits and validates page numbers.
 *
 * @package App\Filters\Common
 */
class PaginationFilter extends AbstractFilter
{
    /**
     * Apply pagination parameters to the query builder.
     * 
     * Validates and normalizes page and per_page parameters, enforcing limits
     * and setting defaults. Stores pagination data on the model for later use
     * by the Filterable trait's getFilteredPaginatedList method.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder with pagination data set
     */
    public function handle(Builder $builder, Closure $next): Builder
    {
        $page = (int) ($this->data[FilterEnums::PARAM_PAGE] ?? 1);
        $perPage = (int) ($this->data[FilterEnums::PARAM_PER_PAGE] ?? FilterEnums::DEFAULT_PER_PAGE);
        
        if ($perPage > FilterEnums::MAX_PER_PAGE) {
            $perPage = FilterEnums::MAX_PER_PAGE;
        }
        
        if ($page < 1) {
            $page = 1;
        }
        
        $model = $builder->getModel();
        $model->paginationData = [
            FilterEnums::PARAM_PAGE => $page,
            FilterEnums::PARAM_PER_PAGE => $perPage
        ];
        
        return $next($builder);
    }
}