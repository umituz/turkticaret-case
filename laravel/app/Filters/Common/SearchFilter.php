<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter extends AbstractFilter
{
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