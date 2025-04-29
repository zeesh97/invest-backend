<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\WithoutWorkflowResource;
use App\Models\WithoutWorkflow;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WithoutWorkflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(WithoutWorkflow::latest()->select(['id', 'name'])->get(), 'Success', Response::HTTP_OK);
            } else {
                return WithoutWorkflowResource::collection(WithoutWorkflow::latest()->paginate());
            }

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'form_id' => [
                    'required',
                    'integer',
                    'exists:forms,id',
                    'unique:without_workflows,form_id,NULL,id,software_category_id,' . $request->software_category_id,
                ],
                'software_category_id' => 'required|integer|exists:software_categories,id',
            ], [
                'form_id.unique' => 'This Form and Software Category combination is already in use.',
            ]);

            $validated['created_by'] = Auth::user()->id;
            $validated['updated_by'] = null;

            $record = WithoutWorkflow::create($validated);

            return Helper::sendResponse($record, 'record created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to create record: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, WithoutWorkflow $withoutWorkflow): JsonResponse
    {
        try {
            $validated = $request->validate([
                'form_id' => [
                    'required',
                    'integer',
                    'exists:forms,id',
                    \Illuminate\Validation\Rule::unique('without_workflows', 'form_id')
                        ->ignore($withoutWorkflow->id)
                        ->where(function ($query) use ($request) {
                            return $query->where('software_category_id', $request->software_category_id);
                        }),
                ],
                'software_category_id' => 'required|integer|exists:software_categories,id',
            ], [
                'form_id.unique' => 'This Form and Software Category combination is already in use.',
            ]);

            $validated['updated_by'] = Auth::user()->id;
            unset($validated['created_by']);

            $withoutWorkflow->update($validated);

            return Helper::sendResponse($withoutWorkflow, 'Workflow updated successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to update workflow: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(WithoutWorkflow $withoutWorkflow): JsonResponse
    {
        try {
            $withoutWorkflow->delete();
            return Helper::sendResponse([], 'Workflow deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to delete workflow: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
