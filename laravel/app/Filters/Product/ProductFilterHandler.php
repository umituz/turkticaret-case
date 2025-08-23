<?php

namespace App\Filters\Product;

use App\Enums\ApiEnums;
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
        $perPage = (int) ($data['per_page'] ?? $data['limit'] ?? ApiEnums::DEFAULT_PAGINATION->value); // Support both per_page and limit for backward compatibility
        
        return $result->paginate($perPage, ['*'], 'page', $page);
    }
}