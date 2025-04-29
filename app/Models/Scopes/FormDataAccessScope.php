<?php

namespace App\Models\Scopes;

use App\Enums\FormEnum;
use App\Models\Department;
use App\Models\Location;
use App\Models\ParallelApprover;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FormDataAccessScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */

    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return;
        }
        $userId = Auth::user()->id;
        $accessibleType = get_class($model);
        $tableName = $model->getTable();
        $formId = FormEnum::getIdByModelName($accessibleType);
        $parallelUserIds = ParallelApprover::where('parallel_user_id', $userId)
            ->pluck('user_id')
            ->toArray();
        $allUserIds = array_unique(array_merge($parallelUserIds, [$userId]));

        $builder->where(function (Builder $query) use ($userId, $tableName, $accessibleType, $formId, $allUserIds) {
            $query->orWhere(function (Builder $subQuery) {
                $subQuery->where('status', 'Approved')
                    ->where(function () {
                        if (Auth::user()->hasPermissionTo('ServiceDesk-view')) {
                            return true;
                        }
                        // return false;
                    });
            })->orWhereExists(function ($subQuery) use ($userId, $tableName, $accessibleType, $allUserIds) {
                $subQuery->from('user_access_levels')
                    ->whereIn('user_id', $allUserIds)
                    ->where('accessible_type', $accessibleType)
                    ->whereColumn('accessible_id', DB::raw("$tableName.id"));
            });
            // Role-Based Access (Department and Location)
            $query->orWhere(function ($subQuery) use ($userId, $tableName, $formId) {
                $subQuery->whereExists(function ($formPermissionQuery) use ($userId, $formId, $tableName) {
                    $formPermissionQuery->select(DB::raw(1))
                        ->from('form_role_user')
                        ->join('form_permissionables', function ($join) use ($formId) {
                            $join->on('form_role_user.form_role_id', '=', 'form_permissionables.form_role_id')
                                ->where('form_permissionables.form_id', $formId);
                        })
                        ->where('form_role_user.user_id', $userId)
                        ->where(function ($roleQuery) use ($tableName) {
                            $roleQuery->where('form_permissionables.form_permissionable_type', Location::class)
                                ->whereColumn('form_permissionables.form_permissionable_id', "$tableName.location_id")
                                ->orWhere('form_permissionables.form_permissionable_type', Department::class)
                                ->whereColumn('form_permissionables.form_permissionable_id', "$tableName.department_id");
                        });
                });
            });
        });
    }
    // $query->leftJoin('user_access_levels', function ($join) use ($userId, $tableName, $accessibleType) {
    //     $join->on('user_access_levels.accessible_id', '=', "$tableName.id")
    //          ->where('user_access_levels.user_id', $userId)
    //          ->where('user_access_levels.accessible_type', $accessibleType);
    // });
    //     $query->orWhereExists(function ($subQuery) use ($userId, $tableName, $accessibleType) {
    //     $subQuery->selectRaw('1')
    //         ->from('user_access_levels')
    //         ->where('user_id', $userId)
    //         ->where('accessible_type', $accessibleType)
    //         ->whereColumn('accessible_id', "$tableName.id");
    // });


    // $query->orWhere(function ($subQuery) use ($userId, $tableName, $formId) {
    //     $formPermissionIds = $this->getFormPermissions($userId, $formId);

    //     $subQuery->where(function ($locationQuery) use ($formPermissionIds, $tableName) {
    //         foreach ($formPermissionIds as $permissionableId => $permissionableType) {
    //             if ($permissionableType === Location::class) {
    //                 $locationQuery->orWhere("$tableName.location_id", $permissionableId);
    //             }
    //         }
    //     })
    //         ->orWhere(function ($departmentQuery) use ($formPermissionIds, $tableName) {
    //             foreach ($formPermissionIds as $permissionableId => $permissionableType) {
    //                 if ($permissionableType === Department::class) {
    //                     $departmentQuery->orWhere("$tableName.department_id", $permissionableId);
    //                 }
    //             }
    //         });
    // });

    // public function apply(Builder $builder, Model $model): void
    // {
    //     if (Auth::check() && Auth::user()->hasRole('admin')) {
    //         return;
    //     }
    //     $userId = Auth::user()->id;
    //     $accessibleType = get_class($model);
    //     $tableName = $model->getTable();
    //     $formId = $model->getModelId();
    //     $builder->where(function ($query) use ($userId, $tableName, $accessibleType, $formId) {

    //         $query->whereExists(function ($subQuery) use ($userId, $tableName, $accessibleType) {
    //             $subQuery->selectRaw('1')
    //                 ->from('user_access_levels')
    //                 ->where('user_id', $userId)
    //                 ->where('accessible_type', $accessibleType)
    //                 ->whereColumn('accessible_id', "$tableName.id");
    //         });
    //         $query->orWhere(function ($subQuery) {
    //             $subQuery->where('status', 'Approved')
    //                 ->where(function ($permissionQuery) {
    //                     if (Auth::user()->hasPermissionTo('ServiceDesk-view')) {
    //                         return true;
    //                     }
    //                     return false;
    //                 });
    //         });
    //         // Role-Based Access (Department and Location)
    //         $query->orWhere(function ($subQuery) use ($userId, $tableName, $formId) {
    //             $formPermissionIds = $this->getFormPermissions($userId, $formId);

    //             $subQuery->where(function ($locationQuery) use ($formPermissionIds, $tableName) {
    //                 foreach ($formPermissionIds as $permissionableId => $permissionableType) {
    //                     if ($permissionableType === Location::class) {
    //                         $locationQuery->orWhere("$tableName.location_id", $permissionableId);
    //                     }
    //                 }
    //             })
    //                 ->orWhere(function ($departmentQuery) use ($formPermissionIds, $tableName) {
    //                     foreach ($formPermissionIds as $permissionableId => $permissionableType) {
    //                         if ($permissionableType === Department::class) {
    //                             $departmentQuery->orWhere("$tableName.department_id", $permissionableId);
    //                         }
    //                     }
    //                 });
    //         });
    //     });
    // }

    // private function getFormPermissions($userId, $formId): array
    // {
    //     $formRoleIds = DB::table('form_role_user')
    //         ->where('user_id', $userId)
    //         ->pluck('form_role_id')
    //         ->toArray();

    //     return DB::table('form_permissionables')
    //         ->where('form_id', $formId)
    //         ->whereIn('form_role_id', $formRoleIds)
    //         ->pluck('form_permissionable_type', 'form_permissionable_id')
    //         ->toArray();
    // }

    /* Raw Query for test */
    /*  SELECT *
FROM scrf
WHERE (
    EXISTS (
        SELECT 1
        FROM user_access_levels
        WHERE user_access_levels.user_id = 22
        AND user_access_levels.accessible_type = 'App\\Models\\Forms\\SCRF'
        AND user_access_levels.accessible_id = scrf.id
    )
    OR (
        scrf.status = 'Approved'
        AND EXISTS (
            SELECT 1
        FROM model_has_permissions
        JOIN permissions ON model_has_permissions.permission_id = permissions.id
        WHERE model_has_permissions.model_id = 22
        AND permissions.name = 'ServiceDesk-view'
        )
    )
    OR (
        (
            scrf.location_id IN (
                SELECT form_permissionable_id
                FROM form_permissionables
                WHERE form_permissionables.form_id = 2
                AND form_permissionables.form_permissionable_type = 'App\\Models\\Location'
                AND form_permissionables.form_role_id IN (
                    SELECT form_role_id
                    FROM form_role_user
                    WHERE form_role_user.user_id = 22
                )
            )
        )
        OR (
            scrf.department_id IN (
                SELECT form_permissionable_id
                FROM form_permissionables
                WHERE form_permissionables.form_id = 2
                AND form_permissionables.form_permissionable_type = 'App\\Models\\Department'
                AND form_permissionables.form_role_id IN (
                    SELECT form_role_id
                    FROM form_role_user
                    WHERE form_role_user.user_id = 22
                )
            )
        )
    )
); */
}
