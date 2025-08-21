<?php

namespace App\Traits;

use App\Filters\Base\FilterAction;
use App\Filters\Base\FilterEnums;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters, array $data): Builder
    {
        return FilterAction::apply($query, $filters, $data);
    }

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