<?php

namespace App\Filters\Product;

use App\Filters\Base\FilterAction;
use Illuminate\Database\Eloquent\Builder;

class ProductFilterHandler
{
    public static function apply(Builder $query, array $data)
    {
        $query->with(['category']);

        // Apply product-specific filters
        $filters = [ProductFilter::class];
        $result = FilterAction::apply($query, $filters, $data);

        // Handle pagination
        $page = (int) ($data['page'] ?? 1);
        $limit = (int) ($data['limit'] ?? 20);
        
        return $result->paginate($limit, ['*'], 'page', $page);
    }
}