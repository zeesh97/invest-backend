<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LocationController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Location-view|Location-create|Location-edit|Location-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Location-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Location-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Location-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Location-view|Location-create|Location-edit|Location-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Location-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Location-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Location-delete', ['only' => ['destroy']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = Location::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = Location::select(['id', 'name'])
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
                'name' => 'required|unique:locations|max:50',
            ], [
                'name.unique' => 'The location already exists.',
            ]);

            $location = Location::create($validated);
            return Helper::sendResponse($location, 'Location created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $form = Location::findOrFail($id);
            return Helper::sendResponse(new LocationResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        try {
            $request->validate([
                'name' => 'required|unique:locations,name,' . $location->id . ',id|max:50',
            ], [
                'name.unique' => 'The location name already exists',
            ]);

            $location->update([
                'name' => $request->input('name'),
            ]);

            return Helper::sendResponse($location, 'Location updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $location = Location::withTrashed()->find($id);

            if (User::withTrashed()->where('location_id', $id)->exists()) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $location->delete();
            return Helper::sendResponse($location, 'Location deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
