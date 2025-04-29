<?php

namespace App\Services;

use App\Models\Scopes\FormDataAccessScope;
use Auth;
use DB;

class FormListService
{
    public function getAll($form, $otherRelationships = [], $sortBy = 'id', $sortOrder = 'desc', $perPage = 15)
    {

        $otherRelationships[] = 'user:id,name';
        $otherRelationships[] = 'updatedBy:id,name';
        $otherRelationships[] = 'department:id,name';
        $otherRelationships[] = 'location:id,name';
        $otherRelationships[] = 'designation:id,name';
        $otherRelationships[] = 'section:id,name';

        $accessibleType = $form['identity'];
        $userId = Auth::user()->id;
        $formId = $form['id'];
        $tableName = $form['tableName'];

        $formRoleIds = DB::table('form_role_user')
            ->where('user_id', $userId)
            ->pluck('form_role_id')
            ->toArray();

        if (count($formRoleIds) > 0) {
            $formPermissionIds = DB::table('form_permissionables')->where('form_id', $formId)
                ->where('form_role_id', $formRoleIds)->pluck('form_permissionable_type', 'form_permissionable_id')->toArray();
        }

        // $data = $accessibleType::withoutGlobalScope(FormDataAccessScope::class)
        //     ->with([
        //         // 'approvalStatuses' => function ($query) {
        //         //     $query->select(
        //         //         'form_id',
        //         //         'key',
        //         //         'approver_id',
        //         //         'user_id',
        //         //         'approval_required',
        //         //         'condition_id',
        //         //         'sequence_no',
        //         //         'responded_by',
        //         //         'status',
        //         //         'editable'
        //         //     )
        //         //         ->with(['approver:id,name', 'user:id,name', 'respondedBy:id,name']);
        //         // },
        //         ...$otherRelationships
        //     ]);
        $query = $accessibleType::withoutGlobalScope(FormDataAccessScope::class)
            ->with($otherRelationships)
            ->select($tableName . '.*');
        if (!empty($formPermissionIds)) {
            $query->where(function ($subQuery) use ($formPermissionIds) {
                foreach ($formPermissionIds as $permissionableId => $permissionableType) {
                    if ($permissionableType === 'App\Models\Department') {
                        $subQuery->orWhere('department_id', $permissionableId);
                    } elseif ($permissionableType === 'App\Models\Location') {
                        $subQuery->orWhere('location_id', $permissionableId);
                    }
                }
            });
        }
        $data = $query
            ->orWhere(function ($query) use ($userId, $tableName, $accessibleType) {
                $query->whereExists(function ($subQuery) use ($userId, $tableName, $accessibleType) {
                    $subQuery->select(DB::raw(1))
                        ->from('user_access_levels')
                        ->where('user_access_levels.user_id', $userId)
                        ->where('user_access_levels.accessible_type', $accessibleType)
                        ->whereColumn('user_access_levels.accessible_id', "$tableName.id");
                });
            })
            // ->whereHas('approvalStatuses', function ($query) use ($tableName, $formId) {
            //     $query->where('form_id', $formId)
            //         ->where('key', DB::raw($tableName . '.id'));
            // })
            // ->select($tableName . '.*')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
        // return $data;
        // dd($data);
        $parts = explode('\\', $accessibleType);
        $resourceClass = 'App\Http\Resources\Index' . end($parts) . 'Resource';
        if (!class_exists($resourceClass)) {
            throw new \Exception("Resource class {$resourceClass} does not exist.");
        }

        return $resourceClass::collection($data);
    }
}
