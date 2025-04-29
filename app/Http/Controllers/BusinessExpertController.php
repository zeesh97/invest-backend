<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\BusinessExpertResource;
use App\Models\BusinessExpert;
use App\Models\Section;
use App\Models\SoftwareCategory;
use App\Models\SoftwareSubcategory;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class BusinessExpertController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:BusinessExpert-view|BusinessExpert-create|BusinessExpert-edit|BusinessExpert-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:BusinessExpert-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:BusinessExpert-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:BusinessExpert-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:BusinessExpert-view|BusinessExpert-create|BusinessExpert-edit|BusinessExpert-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:BusinessExpert-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:BusinessExpert-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:BusinessExpert-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {

                return BusinessExpertResource::collection(BusinessExpert::latest()->get());
            } else {
                return BusinessExpertResource::collection(BusinessExpert::latest()->paginate());
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_CREATED);
        }
    }

    public function getBusinessExperts(Request $request): JsonResponse
    {
        $data['business_experts'] = BusinessExpert::with('users')->where('software_subcategory_id', $request->software_subcategory_id)
            ->where('business_expert_user_id', $request->business_expert_user_id)
            ->get();
        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'software_subcategory_id' => 'required|exists:software_subcategories,id',
                'business_expert_user_id' => 'required|exists:users,id'
            ], [
                'name.unique' => 'The Business Expert already exists.'
            ]);
            $businessExpert = BusinessExpert::create($validated);
            return Helper::sendResponse($businessExpert, 'Business Expert created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_CREATED);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $businessExpert = BusinessExpert::findOrFail($id);
            return Helper::sendResponse(new BusinessExpertResource($businessExpert), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, BusinessExpert $businessExpert)
    {
        try {
            $validated = $request->validate([
                'software_subcategory_id' => 'required|exists:software_subcategories,id',
                'business_expert_user_id' => 'required|exists:users,id',
            ], [
                'name.unique' => 'The Business Expert name already exists',
            ]);
            $businessExpert->update($validated);

            return Helper::sendResponse($businessExpert, 'Business Expert updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_CREATED);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $businessExpert = BusinessExpert::withTrashed()->find($id);

            if (
                User::withTrashed()->where('business_expert_user_id', $id)->exists() ||
                Section::withTrashed()->where('business_expert_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $businessExpert->delete();
            return Helper::sendResponse($businessExpert, 'Business Expert deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
