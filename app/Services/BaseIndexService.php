<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseIndexService
{
    protected $model;
    protected $resource;

    public function __construct($model, $resource)
    {
        $this->model = $model;
        $this->resource = $resource;
    }

    public function filter(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $query = $this->model::query();

        $filters = $this->getFilters();
        foreach ($filters as $filter) {
            $value = $request->query($filter);
            if ($value) {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }

        $relationships = $this->getRelationships();
        foreach ($relationships as $relationship => $column) {
            $value = $request->query($relationship);
            if ($value) {
                $query->whereHas($relationship, function ($query) use ($column, $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                });
            }
        }

        $sortBy = $request->query('sortBy', 'created_at');
        $sortOrder = $request->query('sortOrder', 'desc');
        if (in_array($sortBy, array_merge($filters, ['created_at', 'updated_at'])) && in_array($sortOrder, ['asc', 'desc'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        return $this->resource::collection($query->paginate($perPage));
    }

    protected function getFilters()
    {
        return [];
    }

    protected function getRelationships()
    {
        return [];
    }
}
