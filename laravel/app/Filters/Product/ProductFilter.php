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
        
        // Search in product name and description
        if (!empty($this->data['search'])) {
            $builder->where(function ($query) {
                $search = $this->data['search'];
                $query->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        }
        
        // Active status filter
        if (isset($this->data['is_active'])) {
            $builder->where('is_active', filter_var($this->data['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
        
        // Featured filter
        if (isset($this->data['isFeatured'])) {
            $builder->where('is_featured', filter_var($this->data['isFeatured'], FILTER_VALIDATE_BOOLEAN));
        }
        
        return $next($builder);
    }
}