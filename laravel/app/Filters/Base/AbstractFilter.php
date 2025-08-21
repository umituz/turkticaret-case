<?php

namespace App\Filters\Base;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractFilter implements FilterInterface
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    abstract public function handle(Builder $builder, Closure $next): Builder;
}