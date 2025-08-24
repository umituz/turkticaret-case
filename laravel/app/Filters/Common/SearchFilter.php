<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Search filter for full-text searching across model fields.
 * 
 * Provides flexible search functionality that can search across multiple fields
 * or target a specific field. Uses PostgreSQL's ILIKE operator for case-insensitive
 * searching with wildcard matching. Supports model-defined searchable fields.
 *
 * @package App\Filters\Common
 */
class SearchFilter extends AbstractFilter
{
    /**
     * Apply search filter to the query builder.
     * 
     * Performs text search based on the search parameter. Can search across
     * all searchable fields defined in the model, or target a specific field
     * when searchBy parameter is provided. Uses case-insensitive LIKE matching.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder with applied search conditions
     */
    public function handle(Builder $builder, Closure $next): Builder
    {
        if (!empty($this->data[FilterEnums::PARAM_SEARCH])) {
            $search = $this->data[FilterEnums::PARAM_SEARCH];
            $searchBy = $this->data[FilterEnums::PARAM_SEARCH_BY] ?? null;
            
            $model = $builder->getModel();
            $searchableFields = $model->searchableFields ?? ['name'];
            
            if ($searchBy && in_array($searchBy, $searchableFields)) {
                $builder->where($searchBy, FilterEnums::SEARCH_OPERATOR, FilterEnums::SEARCH_WILDCARD . $search . FilterEnums::SEARCH_WILDCARD);
            } else {
                $builder->where(function ($query) use ($search, $searchableFields) {
                    foreach ($searchableFields as $field) {
                        $query->orWhere($field, FilterEnums::SEARCH_OPERATOR, FilterEnums::SEARCH_WILDCARD . $search . FilterEnums::SEARCH_WILDCARD);
                    }
                });
            }
        }
        
        return $next($builder);
    }
}