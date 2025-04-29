<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\DesignationResource;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DesignationController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Designation-view|Designation-create|Designation-edit|Designation-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Designation-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Designation-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Designation-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Designation-view|Designation-create|Designation-edit|Designation-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Designation-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Designation-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Designation-delete', ['only' => ['destroy']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     try {
    //         if ($request->has('all')) {
    //             return Helper::sendResponse(Designation::latest()->select(['id', 'name'])->get(), 'Success');
    //         } else {
    //             return DesignationResource::collection(Designation::latest()->paginate());
    //         }
    //     } catch (\Exception $e) {
    //         return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
    //     }
    // }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = Designation::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = Designation::select(['id', 'name'])
                    ->latest()
                    ->paginate($perPage);
                return Helper::sendResponse($records, 'Success', 200);
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
                'name' => 'required|unique:designations|max:50',
            ], [
                'name.unique' => 'The designation already exists.',
            ]);

            $designation = Designation::create($validated);
            return Helper::sendResponse($designation, 'Designation created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $designation = Designation::findOrFail($id);
            return Helper::sendResponse(new DesignationResource($designation), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Designation $designation)
    {
        try {
            $request->validate([
                'name' => 'required|unique:designations,name,' . $designation->id . '|max:50',
            ], [
                'name.unique' => 'The designation name already exists',
            ]);
            $designation->update([
                'name' => $request->input('name'),
            ]);
            return Helper::sendResponse($designation, 'Designation updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $designation = Designation::withTrashed()->find($id);

            if (User::withTrashed()->where('designation_id', $id)->exists()) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $designation->delete();
            return Helper::sendResponse($designation, 'Designation deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
