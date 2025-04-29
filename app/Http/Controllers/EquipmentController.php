<?php

namespace App\Http\Controllers;

use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use App\Models\Forms\SRFEquipment;
use Illuminate\Http\Request;
use App\Http\Helpers\Helper;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EquipmentController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Equipment-view|Equipment-create|Equipment-edit|Equipment-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Equipment-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Equipment-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Equipment-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Equipment-view|Equipment-create|Equipment-edit|Equipment-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Equipment-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Equipment-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Equipment-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Equipment::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return EquipmentResource::collection(Equipment::latest()->paginate());
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
            $validated = $request->validate([
                'name' => 'required|unique:equipment|max:50'
            ], [
                'name.unique' => 'The equipment already exists.'
            ]);
            $equipment = Equipment::create($validated);
            return Helper::sendResponse($equipment, 'Equipment created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $equipment = Equipment::findOrFail($id);
            return Helper::sendResponse(new EquipmentResource($equipment), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Equipment $equipment)
    {
        try {
            $request->validate([
                'name' => 'required|unique:equipment,name,' . $equipment->id . '|max:50',
            ], [
                'name.unique' => 'The equipment name already exists',
            ]);
            $equipment->update([
                'name' => $request->input('name'),
            ]);
            return Helper::sendResponse($equipment, 'Equipment updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $equipment = Equipment::find($id);

            if (
                \DB::table('equipment_requests')->where('equipment_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $equipment->delete();
                return Helper::sendResponse($equipment, 'Equipment deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
