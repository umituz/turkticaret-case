<?php

namespace App\Filters\Product;

use App\Filters\Base\AbstractFilter;
use App\Traits\SecureInputTrait;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Product-specific filter for e-commerce product filtering.
 * 
 * Implements comprehensive product filtering including category, price range,
 * search functionality, and status filters. Includes security measures for
 * user input sanitization and SQL injection prevention.
 *
 * @package App\Filters\Product
 */
class ProductFilter extends AbstractFilter
{
    use SecureInputTrait;

    /**
     * Apply product-specific filters to the query builder.
     * 
     * Implements multiple filter types including category filtering, price range
     * filtering, full-text search across name and description, active status
     * filtering, and featured product filtering. All user inputs are sanitized
     * and validated for security.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder with applied product filters
     */
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
                // Sanitize and escape search input for security
                $search = $this->escapeLikeWildcards($this->sanitizeString($this->data['search']));
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
