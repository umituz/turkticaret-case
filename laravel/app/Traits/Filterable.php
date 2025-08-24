<?php

namespace App\Traits;

use App\Filters\Base\FilterAction;
use App\Filters\Base\FilterEnums;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filterable trait for applying filters to Eloquent models.
 * 
 * Provides filtering and pagination capabilities to Eloquent models through
 * a standardized filter system. Includes scope methods for applying filters
 * and retrieving paginated, filtered results with eager loading support.
 *
 * @package App\Traits
 */
trait Filterable
{
    /**
     * Apply filters to the query using the filter pipeline.
     * 
     * This scope method enables models to use the filtering system by applying
     * multiple filters in sequence through the pipeline pattern. Each filter
     * receives the request data and can modify the query accordingly.
     *
     * @param Builder $query The Eloquent query builder instance
     * @param array $filters Array of filter class names to apply
     * @param array $data Request data containing filter parameters
     * @return Builder The modified query builder with applied filters
     */
    public function scopeFilter(Builder $query, array $filters, array $data): Builder
    {
        return FilterAction::apply($query, $filters, $data);
    }

    /**
     * Get filtered and paginated results with optional eager loading.
     * 
     * Combines filtering, pagination, and eager loading in a single method.
     * Applies the specified filters to the model query, loads requested relations,
     * and returns either paginated results or a collection based on pagination data.
     *
     * @param array $filters Array of filter class names to apply
     * @param array $data Request data containing filter and pagination parameters
     * @param array $relations Array of relation names to eager load
     * @return mixed Paginated results if pagination data exists, otherwise collection
     */
    public static function getFilteredPaginatedList(array $filters, array $data, array $relations = [])
    {
        $query = static::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        $result = $query->filter($filters, $data);

        $model = $result->getModel();
        if (isset($model->paginationData)) {
            $perPage = $model->paginationData[FilterEnums::PARAM_PER_PAGE];
            $page = $model->paginationData[FilterEnums::PARAM_PAGE];
            return $result->paginate($perPage, ['*'], FilterEnums::PARAM_PAGE, $page);
        }

        return $result->get();
    }
}