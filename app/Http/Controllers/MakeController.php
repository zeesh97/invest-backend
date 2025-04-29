<?php

namespace App\Http\Controllers;

use App\Http\Resources\MakeResource;
use App\Models\Make;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Helpers\Helper;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MakeController extends Controller
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
    //             return MakeResource::collection(Department::latest()->paginate());
    //         }
    //     } catch (\Exception $e) {
    //         return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
    //     }
    // }
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = Make::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = Make::select(['id', 'name'])
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
                'name.unique' => 'The Make already exists.'
            ]);
            $make = Make::create($validated);
            return Helper::sendResponse($make, 'Make created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $make = Make::findOrFail($id);
            return Helper::sendResponse(new MakeResource($make), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Make $make)
    {
        try {
            $request->validate([
                'name' => 'required|unique:makes,name,' . $make->id . '|max:50',
            ], [
                'name.unique' => 'The Make name already exists',
            ]);
            $make->update([
                'name' => $request->input('name'),
            ]);
            return Helper::sendResponse($make, 'Make updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $make = Make::find($id);

            if (
                User::withTrashed()->where('department_id', $id)->exists() ||
                Section::withTrashed()->where('department_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $make->delete();
                return Helper::sendResponse($make, 'Make deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
