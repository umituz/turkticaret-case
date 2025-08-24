<?php

namespace App\Filters\Product;

use App\Enums\ApiEnums;
use App\Filters\Base\FilterAction;
use Illuminate\Database\Eloquent\Builder;

/**
 * Product-specific filter handler for comprehensive product filtering.
 * 
 * Orchestrates the application of product-specific filters with eager loading
 * and pagination. Provides a centralized entry point for all product filtering
 * operations including category, price, search, and status filters.
 *
 * @package App\Filters\Product
 */
class ProductFilterHandler
{
    /**
     * Apply comprehensive product filtering with eager loading and pagination.
     * 
     * Combines product-specific filtering with category eager loading and
     * automatic pagination. Supports both 'per_page' and 'limit' parameters
     * for backward compatibility with existing API consumers.
     *
     * @param Builder $query The product query builder instance
     * @param array $data Array of filter parameters including pagination settings
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Paginated filtered results
     */
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