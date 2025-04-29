<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\FormPermissionRequest;
use App\Http\Resources\FormPermissionResource;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\FormPermission;
use App\Models\FormPermissionable;
use App\Models\FormRole;
use App\Models\FormRoleUser;
use App\Models\Forms\SCRF;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class FormPermissionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('perPage', 10);
                $search = $request->query('search', '');
                if (Auth::user()->hasRole('admin')) {
                return FormPermissionResource::collection(FormRole::
                whereHas('formPermissions', function (Builder $query) use ($search): void {
                    $query->whereLike([
                        'name',
                        'formPermissionable.name',
                    ], $search);
                })->latest()->paginate($perPage));
            }

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(Request $request)
    {
        return \DB::transaction(function () use ($request) {
            if (Auth::user()->hasRole('admin')) {
                $validated = $request->validate([
                    'name' => ['required', 'string', 'min:2', 'max:40', 'unique:form_roles,name'],
                    'form_ids' => ['required', 'array'],
                    'form_ids.*' => ['required', 'exists:forms,id'],
                    'dependent_keys' => ['required', 'array'],
                    'dependent_keys.*' => ['required', Rule::in([1, 2])],
                    'dependent_ids' => [
                        'required',
                        'array',
                        Rule::when(function () use ($request) {
                            return $request->dependent_keys == 1;
                        }, [
                            'required',
                            'exists:departments,id'
                        ]),
                        Rule::when(function () use ($request) {
                            return $request->dependent_keys == 2;
                        }, [
                            'required',
                            'exists:locations,id'
                        ]),
                    ],
                ]);

                $role = new FormRole();
                $role->name = $validated['name'];
                $role->save();

                $formPermissionables = [];
                foreach ($validated['form_ids'] as $key => $formId) {
                    $currentTime = Carbon::now();

                    $formPermissionables[] = [
                        'form_role_id' => $role->id,
                        'form_id' => $formId,
                        'form_permissionable_type' => 'App\Models\\' . ($validated['dependent_keys'][$key] == 1 ? 'Department' : 'Location'),
                        'form_permissionable_id' => $validated['dependent_ids'][$key],
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime,
                    ];
                }

                $response = FormPermissionable::insert($formPermissionables);
                if (!$response) {
                    return Helper::sendError('Failed to add form permission', [], 403);
                }
                return Helper::sendResponse($role, 'Success', 201);
            } else {
                return Helper::sendError('Unauthorized', [], 403);
            }
        });

    }

    public function update(Request $request, int $id)
    {
        $formRole = FormRole::find($id);
        if (!$formRole) {
            return Helper::sendError('No Record found.', $formRole, 404);
        }
        return \DB::transaction(function () use ($request, $formRole) {
            if (Auth::user()->hasRole('admin')) {
                $validated = $request->validate([
                    'name' => ['required', 'string', 'min:2', 'max:40', Rule::unique('form_roles', 'name')->ignore($formRole->id)],
                    'form_ids' => ['required', 'array'],
                    'form_ids.*' => ['required', 'exists:forms,id'],
                    'dependent_keys' => ['required', 'array'],
                    'dependent_keys.*' => ['required', Rule::in([1, 2])],
                    'dependent_ids' => [
                        'required',
                        'array',
                        Rule::when(function () use ($request) {
                            return $request->dependent_keys == 1;
                        }, [
                            'required',
                            'exists:departments,id'
                        ]),
                        Rule::when(function () use ($request) {
                            return $request->dependent_keys == 2;
                        }, [
                            'required',
                            'exists:locations,id'
                        ]),
                    ],
                ]);

                $formRole->name = $validated['name'];
                $formRole->update();

                FormPermissionable::where('form_role_id', $formRole->id)->delete();

                $formPermissionables = [];
                foreach ($validated['form_ids'] as $key => $formId) {
                    $currentTime = Carbon::now();

                    $formPermissionables[] = [
                        'form_role_id' => $formRole->id,
                        'form_id' => $formId,
                        'form_permissionable_type' => 'App\Models\\' . ($validated['dependent_keys'][$key] == 1 ? 'Department' : 'Location'),
                        'form_permissionable_id' => $validated['dependent_ids'][$key],
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime,
                    ];
                }

                $response = FormPermissionable::insert($formPermissionables);
                if (!$response) {
                    return Helper::sendError('Failed to add form permission', [], 403);
                }
                return Helper::sendResponse($formRole, 'Success', 201);
            } else {
                return Helper::sendError('Unauthorized', [], 403);
            }
        });

    }

    public function destroy($id)
    {
        try {
            $formPermission = FormRole::findOrFail($id);
            if (!$formPermission) {
                return Helper::sendError('No Record found.', [], 404);
            }

            if (!Auth::user()->hasRole('admin')) {
                return Helper::sendError('Unauthorized', [], 403);
            }

            $exists = DB::table('form_role_user')->where('form_role_id', $id)->exists();

            if ($exists) {
                return Helper::sendError("This Form permission is already assigned", [], 403);
            }
            DB::beginTransaction();

            DB::transaction(function () use ($id, $formPermission) {
                FormPermissionable::where('form_role_id', $id)->delete();

                $formPermission->delete();
            });

            DB::commit();

            return Helper::sendResponse([], 'Form Permission deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return Helper::sendError('Failed to delete Form Permission: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }


}
