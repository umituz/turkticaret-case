<?php

namespace App\Filters\Base;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;

/**
 * Filter action orchestrator for applying multiple filters to query builders.
 *
 * This class manages the execution of filter pipelines using Laravel's Pipeline
 * pattern. It coordinates the application of multiple filters to a query builder
 * in a sequential, chainable manner.
 *
 * @package App\Filters\Base
 */
class FilterAction
{
    /**
     * Apply a series of filters to a query builder using Laravel's pipeline pattern.
     *
     * Takes a query builder instance and applies multiple filters in sequence
     * using the pipeline pattern. Each filter receives the request data and
     * can modify the query before passing it to the next filter in the chain.
     *
     * @param Builder $query The Eloquent query builder to apply filters to
     * @param array $filters Array of filter class names to be applied
     * @param array $data Request data containing filter parameters
     * @return Builder The modified query builder after all filters are applied
     * @throws BindingResolutionException
     */
    public static function apply(Builder $query, array $filters, array $data): Builder
    {
        return App::make(Pipeline::class)
            ->send($query)
            ->through(array_map(function ($filter) use ($data) {
                return app()->makeWith($filter, ['data' => $data]);
            }, $filters))
            ->thenReturn();
    }
}
