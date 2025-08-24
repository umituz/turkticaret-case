<?php

namespace App\Filters\Base;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for filter implementations.
 * 
 * Defines the required method signature for all filter classes that participate
 * in the query filtering pipeline pattern. Each filter must implement the handle
 * method to apply specific filtering logic to the Eloquent query builder.
 *
 * @package App\Filters\Base
 */
interface FilterInterface
{
    /**
     * Apply the filter to the query builder.
     * 
     * This method receives a query builder and a closure representing the next
     * filter in the pipeline. It should apply its filtering logic and then
     * call the next filter in the chain.
     *
     * @param Builder $builder The Eloquent query builder instance
     * @param Closure $next The next filter in the pipeline
     * @return Builder The modified query builder
     */
    public function handle(Builder $builder, Closure $next): Builder;
}