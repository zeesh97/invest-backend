<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Role-view|Role-create|Role-edit|Role-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Role-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Role-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Role-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Role-view|Role-create|Role-edit|Role-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Role-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Role-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Role-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Role::with('permissions')->latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return RoleResource::collection(Role::latest()->paginate());
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|unique:roles,name|max:50',
            ], [
                'name.unique' => 'The role name already exists.',
            ]);
            $role = Role::create([
                'guard_name' => 'web',
                'name' => $request->name,
            ]);
            $role->syncPermissions($request->permissions);
            return Helper::sendResponse(new RoleResource($role), 'Role created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $form = Role::findOrFail($id);
            return Helper::sendResponse(new RoleResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|unique:roles,name,' . $role->id . '|max:50',
            ], [
                'name.unique' => 'The role name already exists',
            ]);
            if($role->id !== 1){
                $role->update([
                    'guard_name' => 'web',
                    'name' => $request->name,
                ]);
                $role->syncPermissions($request->permissions);
            }
            return Helper::sendResponse(new RoleResource($role), 'Role updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();
        if ($user->hasRole('admin') || $id == $user->id) {
            try {
                $role = Role::findOrFail($id);
                $role->delete();
                return Helper::sendResponse($role, 'Role deleted successfully');
            } catch (\Exception $e) {
                return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            return Helper::sendError('Unauthorized action.', 'error', Response::HTTP_UNAUTHORIZED);
        }
    }
}
