<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\SoftwareCategoryResource;
use App\Models\Forms\SCRF;
use App\Models\SoftwareCategory;
use App\Models\SoftwareSubcategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class SoftwareCategoryController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:SoftwareCategory-view|SoftwareCategory-create|SoftwareCategory-edit|SoftwareCategory-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:SoftwareCategory-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:SoftwareCategory-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:SoftwareCategory-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:SoftwareCategory-view|SoftwareCategory-create|SoftwareCategory-edit|SoftwareCategory-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:SoftwareCategory-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:SoftwareCategory-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:SoftwareCategory-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = SoftwareCategory::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = SoftwareCategory::select(['id', 'name'])
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

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|unique:software_categories|max:60'
            ], [
                'name.unique' => 'The Software Category already exists.'
            ]);
            $softwareCategory = SoftwareCategory::create($validated);
            return Helper::sendResponse($softwareCategory, 'Software Category created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $form = SoftwareCategory::findOrFail($id);
            return Helper::sendResponse(new SoftwareCategoryResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, SoftwareCategory $softwareCategory)
    {
        try {
            $request->validate([
                'name' => 'required|unique:software_categories,name,' . $softwareCategory->id . '|max:50'
            ], [
                'name.unique' => 'The Software Category name already exists',
            ]);
            $softwareCategory->update([
                'name' => $request->input('name')
            ]);
            return Helper::sendResponse($softwareCategory, 'Software Category updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $softwareCategory = SoftwareCategory::find($id);

            if (
                SCRF::where('software_category_id', $id)->exists() ||
                SoftwareSubcategory::where('software_category_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $softwareCategory->delete();
            return Helper::sendResponse($softwareCategory, 'Software Category deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
