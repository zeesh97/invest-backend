<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Helpers\Helper;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DepartmentController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Department-view|Department-create|Department-edit|Department-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Department-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Department-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Department-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Department-view|Department-create|Department-edit|Department-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Department-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Department-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Department-delete', ['only' => ['destroy']]);
        }
    }

    // public function index(Request $request)
    // {
    //     try {
    //         if ($request->has('all')) {
    //             return Helper::sendResponse(Department::latest()->select(['id', 'name'])->get(), 'Success');
    //         } else {
    //             return DepartmentResource::collection(Department::latest()->paginate());
    //         }
    //     } catch (\Exception $e) {
    //         return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
    //     }
    // }
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = Department::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = Department::select(['id', 'name'])
                    ->latest()
                    ->paginate($perPage);
                return $records;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch cost centers: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|unique:departments|max:50'
            ], [
                'name.unique' => 'The department already exists.'
            ]);
            $department = Department::create($validated);
            return Helper::sendResponse($department, 'Department created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $department = Department::findOrFail($id);
            return Helper::sendResponse(new DepartmentResource($department), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Department $department)
    {
        try {
            $request->validate([
                'name' => 'required|unique:departments,name,' . $department->id . '|max:50',
            ], [
                'name.unique' => 'The department name already exists',
            ]);
            $department->update([
                'name' => $request->input('name'),
            ]);
            return Helper::sendResponse($department, 'Department updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $department = Department::find($id);

            if (
                User::withTrashed()->where('department_id', $id)->exists() ||
                Section::withTrashed()->where('department_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $department->delete();
                return Helper::sendResponse($department, 'Department deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
