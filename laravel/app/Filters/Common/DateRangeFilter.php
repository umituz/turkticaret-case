<?php

namespace App\Filters\Common;

use App\Filters\Base\AbstractFilter;
use App\Filters\Base\FilterEnums;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter extends AbstractFilter
{
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