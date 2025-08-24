<?php

namespace App\Filters\Base;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Abstract base class for all query filters.
 * 
 * Provides the foundation for implementing the pipeline pattern for query filtering.
 * All concrete filter classes should extend this abstract class and implement the
 * handle method to apply specific filtering logic to Eloquent query builders.
 *
 * @package App\Filters\Base
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * The filter data array containing filter parameters.
     *
     * @var array
     */
    protected array $data;

    /**
     * Create a new filter instance.
     *
     * @param array $data The filter data containing filter parameters
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Apply the filter to the query builder.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder
     */
    abstract public function handle(Builder $builder, Closure $next): Builder;
}