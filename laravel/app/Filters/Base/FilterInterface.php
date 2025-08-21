<?php

namespace App\Filters\Base;

use Closure;
use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function handle(Builder $builder, Closure $next): Builder;
}