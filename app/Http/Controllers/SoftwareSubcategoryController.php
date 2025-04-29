<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\SoftwareSubcategoryResource;
use App\Models\BusinessExpert;
use App\Models\Forms\SCRF;
use App\Models\SoftwareSubcategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SoftwareSubcategoryController extends Controller
{

    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:SoftwareSubcategory-view|SoftwareSubcategory-create|SoftwareSubcategory-edit|SoftwareSubcategory-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:SoftwareSubcategory-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:SoftwareSubcategory-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:SoftwareSubcategory-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:SoftwareSubcategory-view|SoftwareSubcategory-create|SoftwareSubcategory-edit|SoftwareSubcategory-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:SoftwareSubcategory-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:SoftwareSubcategory-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:SoftwareSubcategory-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(SoftwareSubcategory::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                $perPage = $request->get('per_page', 10);
                $records = SoftwareSubcategory::with([
                    'software_category' => function ($query) {
                        $query->select('id', 'name');
                    }
                ])
                ->select(['id', 'name', 'software_category_id'])
                ->latest()
                ->paginate($perPage);

            return Helper::sendResponse($records, 'Success', 200);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|unique:software_subcategories|max:60',
                'software_category_id' => 'required|exists:software_categories,id'
            ], [
                'name.unique' => 'The Software Subcategory already exists.'
            ]);
            $softwareSubcategory = SoftwareSubcategory::create($validated);
            return Helper::sendResponse($softwareSubcategory, 'Software Subcategory created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $form = SoftwareSubcategory::findOrFail($id);
            return Helper::sendResponse(new SoftwareSubcategoryResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, SoftwareSubcategory $softwareSubcategory)
    {
        try {
            $request->validate([
                'name' => 'required|unique:software_subcategories,name,' . $softwareSubcategory->id . '|max:50',
                'software_category_id' => 'required|exists:software_categories,id'
            ], [
                'name.unique' => 'The Software Subcategory name already exists',
            ]);
            $softwareSubcategory->update([
                'name' => $request->input('name'),
                // 'software_category_id' => $request->input('software_category_id')
            ]);
            return Helper::sendResponse($softwareSubcategory, 'Software Subcategory updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $softwareSubcategory = SoftwareSubcategory::find($id);

            if (
                SCRF::whereHas('software_subcategories', function ($query) use ($id) {
                    $query->where('software_subcategory_id', $id);
                })
                ->exists() ||
                BusinessExpert::where('software_subcategory_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $softwareSubcategory->delete();
                return Helper::sendResponse($softwareSubcategory, 'Software Sub-Category deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
