<?php

namespace App\Http\Controllers;

use App\Http\Resources\MdmCategoryResource;
use App\Models\MdmCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Helpers\Helper;
use App\Models\Forms\MasterDataManagementForm;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MdmCategoryController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:MdmCategory-view|MdmCategory-create|MdmCategory-edit|MdmCategory-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:MdmCategory-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:MdmCategory-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:MdmCategory-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:MdmCategory-view|MdmCategory-create|MdmCategory-edit|MdmCategory-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:MdmCategory-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:MdmCategory-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:MdmCategory-delete', ['only' => ['destroy']]);
        }
    }

    // public function index(Request $request)
    // {
    //     try {
    //         if ($request->has('all')) {
    //             return Helper::sendResponse(MdmCategory::latest()->select(['id', 'name'])->get(), 'Success');
    //         } else {
    //             return MdmCategoryResource::collection(MdmCategory::latest()->paginate());
    //         }
    //     } catch (\Exception $e) {
    //         return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
    //     }
    // }
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = MdmCategory::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = MdmCategory::select(['id', 'name'])
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
                'name' => 'required|unique:mdm_categories|max:50'
            ], [
                'name.unique' => 'The MDM Category already exists.'
            ]);
            $mdmCategory = MdmCategory::create($validated);
            return Helper::sendResponse($mdmCategory, 'MdmCategory created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $mdmCategory = MdmCategory::findOrFail($id);
            return Helper::sendResponse(new MdmCategoryResource($mdmCategory), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, MdmCategory $mdmCategory)
    {
        try {
            $request->validate([
                'name' => 'required|unique:mdm_categories,name,' . $mdmCategory->id . '|max:50',
            ], [
                'name.unique' => 'The MDM Category name already exists',
            ]);
            $mdmCategory->update([
                'name' => $request->input('name'),
            ]);
            return Helper::sendResponse($mdmCategory, 'MdmCategory updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $mdmCategory = MdmCategory::find($id);

            if (
                MasterDataManagementForm::withTrashed()->where('mdm_category_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $mdmCategory->delete();
                return Helper::sendResponse($mdmCategory, 'MdmCategory deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
