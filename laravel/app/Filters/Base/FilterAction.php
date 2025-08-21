<?php

namespace App\Filters\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;

class FilterAction
{
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