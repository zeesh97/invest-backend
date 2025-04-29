<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Helpers\Helper;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Service-view|Service-create|Service-edit|Service-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Service-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Service-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Service-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Service-view|Service-create|Service-edit|Service-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Service-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Service-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Service-delete', ['only' => ['destroy']]);
        }
    }
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = Service::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = Service::select(['id', 'name'])
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
                'name' => 'required|unique:services|max:50'
            ], [
                'name.unique' => 'The service already exists.'
            ]);
            $service = Service::create($validated);
            return Helper::sendResponse($service, 'Service created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $service = Service::findOrFail($id);
            return Helper::sendResponse(new ServiceResource($service), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Service $service)
    {
        try {
            $request->validate([
                'name' => 'required|unique:services,name,' . $service->id . '|max:50',
            ], [
                'name.unique' => 'The service name already exists',
            ]);
            $service->update([
                'name' => $request->input('name'),
            ]);
            return Helper::sendResponse($service, 'Service updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $service = Service::find($id);

            if (
                User::withTrashed()->where('service_id', $id)->exists() ||
                Section::withTrashed()->where('service_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $service->delete();
                return Helper::sendResponse($service, 'Service deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
