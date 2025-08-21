<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortFilter extends AbstractFilter
{
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
            $builder->orderBy($orderBy, $order);
        } else {
            $builder->orderBy(FilterEnums::DEFAULT_ORDER_BY, FilterEnums::DEFAULT_ORDER);
        }
        
        return $next($builder);
    }
}