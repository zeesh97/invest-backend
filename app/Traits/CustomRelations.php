<?php

namespace App\Traits;

use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use Illuminate\Database\Eloquent\Builder;

trait CustomRelations
{
    private function getFormData(
        int $form_id,
        ?array $key = null,
        array $with = null,
        $perPage = 10,
        array $where = [],
        array $searchFilters = [],
        array $relationships = [],
        $sortBy = null,
        $sortOrder = null
    ) {
        try {

            $formIdentity = Form::findOrFail($form_id)->value('identity');
            // dd($form);
            // if ($form === null) {
            //     throw new \Exception('Form not found');
            // }

            if ($with !== null) {
                $result = $formIdentity::withoutGlobalScope(FormDataAccessScope::class)->with($with);
            } else {
                $result = $formIdentity::withoutGlobalScope(FormDataAccessScope::class);
            }
            if (!empty($where) && is_array($where)) {
                foreach ($where as $column => $value) {
                    $result = $result->where($column, $value);
                }
            }
            // for search Filters
            if (!empty($searchFilters) && is_array($searchFilters)) {
                foreach ($searchFilters as $filter => $value) {
                    if ($value) {
                        $result = $result->where($filter, 'LIKE', '%' . $value . '%');
                    }
                }
            }
            // Apply relationships filters
            if (!empty($relationships) && is_array($relationships)) {
                foreach ($relationships as $relationship => $value) {
                    if ($value) {
                        $result = $result->whereHas($relationship, function ($query) use ($value) {
                            $query->where('name', 'LIKE', '%' . $value . '%');
                        });
                    }
                }
            }

            if ($sortBy && $sortOrder) {
                $columnMappings = [
                    'task_initiated_at' => 'created_at',
                    'task_assigned_at' => 'assignedTask.created_at',
                    'task_approval_at' => 'approvedTask.created_at',
                    'task_status' => 'taskStatusName.name',
                    'location' => 'location.name',
                    'department' => 'department.name',
                    // Add more mappings as needed
                ];
                $sortBy = $columnMappings[$sortBy] ?? $sortBy;
                if (strpos($sortBy, '.') !== false) {
                    // Extract relationship and column name
                    $relationship = explode('.', $sortBy)[0];
                    $column = explode('.', $sortBy)[1];

                    $result = $result->whereHas($relationship, function (Builder $query) use ($column, $sortOrder) {
                        $query->orderBy($column, $sortOrder);
                    });

                } else {
                    $sortBy = $sortBy;
                    $result = $result->orderBy($sortBy, $sortOrder);
                }
            }
            // for search Filters end

            if ($key !== null) {
                $result = is_array($key) ? $result->whereIn('id', $key)->paginate($perPage) : $result->find($key);
            } else {
                $result = $result->paginate($perPage);
            }

            if ($result === null) {
                throw new \Exception('Record not found');
            }

            return $result;
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
