<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
            // Secure column name escaping to prevent SQL injection
            $escapedColumn = DB::getQueryGrammar()->wrap($orderBy);
            $builder->orderBy(DB::raw($escapedColumn), $order);
        } else {
            $builder->orderBy(FilterEnums::DEFAULT_ORDER_BY, FilterEnums::DEFAULT_ORDER);
        }
        
        return $next($builder);
    }
}