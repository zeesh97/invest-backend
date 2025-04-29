<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function __construct()
    {
        if(request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Project-view|Project-create|Project-edit|Project-delete', ['only' => ['index','show']]);
            $this->middleware('role_or_permission:Project-create', ['only' => ['create','store']]);
            $this->middleware('role_or_permission:Project-edit', ['only' => ['edit','update']]);
            $this->middleware('role_or_permission:Project-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Project-view|Project-create|Project-edit|Project-delete', ['only' => ['index','show']]);
            $this->middleware('role_or_permission:Project-create', ['only' => ['create','store']]);
            $this->middleware('role_or_permission:Project-edit', ['only' => ['edit','update']]);
            $this->middleware('role_or_permission:Project-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Project::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return ProjectResource::collection(Project::latest()->paginate());
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
            'name' => 'required|max:60|unique:projects,name,NULL,id,form_id,' . $request->form_id,
            'description' => 'nullable|string|max:1000',
            'form_id' => 'required|exists:forms,id'
        ], [
            'name.unique' => 'A project with this name already exists for the selected form.'
        ]);
            $project = Project::create($validated);
            return Helper::sendResponse($project, 'Project created successfully');

        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            return Helper::sendResponse(new ProjectResource($project), 'Success', 200);

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
        $request->validate([
            'name' => [
            'required',
            'max:50',
            \Illuminate\Validation\Rule::unique('projects', 'name')->ignore($project->id)->where(function ($query) use ($request) {
                return $query->where('form_id', $request->form_id);
            }),
        ],
        'description' => 'nullable|string|max:1000',
        'form_id' => 'required|exists:forms,id'
        ], [
            'name.unique' => 'A project with this name already exists for the selected form.', // Updated error message
        ]);
        $project->update([
            'name' => $request->input('name'),
            'form_id' => $request->input('form_id'),
        ]);
            return Helper::sendResponse($project, 'Project updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $project = Project::find($id);

            // if (User::where('project_id', $id)->exists()) {
            //     return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            // }
            $project->delete();
            return Helper::sendResponse($project, 'Project deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function projectByFormId(Request $request)
    {
        try {
            $validated = $request->validate([
                'form_id' => 'required|exists:forms,id'
            ]);
            if ($validated) {
                return Helper::sendResponse(Project::where('form_id', $request->form_id)->latest()->select(['id', 'name'])->get(), 'Success');
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

}
