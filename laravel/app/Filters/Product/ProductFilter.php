<?php

namespace App\Filters\Product;

use App\Filters\Base\AbstractFilter;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends AbstractFilter
{
    public function handle(Builder $builder, Closure $next): Builder
    {
        // Category filter
        if (!empty($this->data['category_uuid'])) {
            $builder->where('category_uuid', $this->data['category_uuid']);
        }
        
        // Price range filter
        if (!empty($this->data['min_price'])) {
            $builder->where('price', '>=', $this->data['min_price']);
        }
        
        if (!empty($this->data['max_price'])) {
            $builder->where('price', '<=', $this->data['max_price']);
        }
        
        // Search in product name
        if (!empty($this->data['search'])) {
            $builder->where('name', 'ILIKE', '%' . $this->data['search'] . '%');
        }
        
        return $next($builder);
    }
}