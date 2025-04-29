<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\PermissionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Permission-view|Permission-create|Permission-edit|Permission-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Permission-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Permission-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Permission-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Permission-view|Permission-create|Permission-edit|Permission-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Permission-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Permission-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Permission-delete', ['only' => ['destroy']]);
        }
    }

    public function index()
    {
        $permissions = Permission::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|unique:permissions,name|max:50',
            ], [
                'name.unique' => 'The permission name already exists.',
            ]);
            $permission = Permission::create([
                'guard_name' => 'web',
                'name' => $request->name,
            ]);
            return Helper::sendResponse($permission, 'Permission created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $form = Permission::findOrFail($id);
            return Helper::sendResponse(new PermissionResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Permission $permission)
    {
        try {
            $request->validate([
                'name' => 'required|unique:permissions,name,' . $permission->id . '|max:50',
            ], [
                'name.unique' => 'The permission name already exists',
            ]);
            $permission->update([
                'guard_name' => 'web',
                'name' => $request->name,
            ]);
            return Helper::sendResponse($permission, 'Permission updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            try {
                $permission = Permission::findOrFail($id);
                $permission->delete();
                return Helper::sendResponse($permission, 'Permission deleted successfully');
            } catch (\Exception $e) {
                return Helper::sendError($e->getMessage(), [], 422);
            }
        } else {
            return Helper::sendError('Unauthorized action.', 'error', Response::HTTP_UNAUTHORIZED);
        }
    }
}
