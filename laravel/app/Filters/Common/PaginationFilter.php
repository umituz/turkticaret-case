<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class PaginationFilter extends AbstractFilter
{
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