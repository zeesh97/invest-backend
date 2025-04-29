<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SectionController extends Controller
{
    public function __construct()
    {
        if(request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Section-view|Section-create|Section-edit|Section-delete', ['only' => ['index','show']]);
            $this->middleware('role_or_permission:Section-create', ['only' => ['create','store']]);
            $this->middleware('role_or_permission:Section-edit', ['only' => ['edit','update']]);
            $this->middleware('role_or_permission:Section-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Section-view|Section-create|Section-edit|Section-delete', ['only' => ['index','show']]);
            $this->middleware('role_or_permission:Section-create', ['only' => ['create','store']]);
            $this->middleware('role_or_permission:Section-edit', ['only' => ['edit','update']]);
            $this->middleware('role_or_permission:Section-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Section::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return SectionResource::collection(Section::latest()->paginate());
            }

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : JsonResponse
    {
        try {
        $validated = $request->validate([
            'name' => 'required|unique:sections|max:60',
            'department_id' => 'required|exists:departments,id'
        ], [
            'name.unique' => 'The section already exists.'
        ]);
            $section = Section::create($validated);
            return Helper::sendResponse($section, 'Section created successfully');

        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $section = Section::findOrFail($id);
            return Helper::sendResponse(new SectionResource($section), 'Success', 200);

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Section $section)
    {
        try {
        $request->validate([
            'name' => 'required|unique:sections,name,' . $section->id . '|max:50',
            'department_id' => 'required|exists:departments,id'
            ], [
                'name.unique' => 'The section name already exists',
            ]);
        $section->update([
            'name' => $request->input('name'),
            'department_id' => $request->input('department_id'),
        ]);
            return Helper::sendResponse($section, 'Section updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $section = Section::withTrashed()->find($id);

            if (User::withTrashed()->where('section_id', $id)->exists()) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $section->delete();
            return Helper::sendResponse($section, 'Section deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function departmentBySectionId(Request $request)
    {
        try {
            $validated = $request->validate([
                'department_id' => 'required|exists:departments,id'
            ]);
            if ($validated) {
                return Helper::sendResponse(Section::where('department_id', $request->department_id)->latest()->select(['id', 'name'])->get(), 'Success');
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }


}
