<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\Forms\MasterDataManagementForm;
use App\Models\ProjectMDM;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ProjectMDMController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:ProjectMDM-view|ProjectMDM-create|ProjectMDM-edit|ProjectMDM-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:ProjectMDM-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:ProjectMDM-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:ProjectMDM-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $projectMDMs = ProjectMDM::latest()->select(['id', 'name'])->get();
                return Helper::sendResponse($projectMDMs, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $projectMDMs = ProjectMDM::with('mdmCategory:id,name', 'softwareCategory:id,name')
                    ->select('id', 'name', 'mdm_category_id', 'software_category_id')
                    ->latest()
                    ->paginate($perPage);
                return Helper::sendResponse($projectMDMs, 'Success', 200);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch MDM Projects: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(
                [
                    'mdm_category_id' => 'required|exists:mdm_categories,id',
                    'software_category_id' => 'required|exists:software_categories,id',
                    'name' => [
                        'required',
                        'string',
                        'min:2',
                        'max:255',
                        Rule::unique('project_mdm_software_categories')
                            ->where('mdm_category_id', $request->mdm_category_id)
                            ->where('software_category_id', $request->software_category_id)
                    ],
                ],
                [
                    'project.unique' => 'The combination of MDM Category, Software Category, and Project must be unique for this MDM Project.',
                ]
            );
            DB::beginTransaction();
            $projectMDM = ProjectMDM::create($validated);
            DB::commit();
            return Helper::sendResponse($projectMDM, 'Project Of Master Data Management Form created successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            return Helper::sendError($e->getMessage(), $e->errors(), HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return Helper::sendError('An error occurred while creating the MDM Project.', [], 500); // Use 500 for internal server errors
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $projectMDM = ProjectMDM::with(['softwareCategory', 'mdmCategory'])->findOrFail($id);
            if ($projectMDM) {
                $validated = $request->validate([
                    'mdm_category_id' => 'required|exists:mdm_categories,id',
                    'software_category_id' => 'required|exists:software_categories,id',
                    'name' => [
                        'required',
                        'string',
                        'min:2',
                        'max:255',
                        Rule::unique('project_mdm_software_categories')
                            ->where('mdm_category_id', $request->mdm_category_id)
                            ->where('software_category_id', $request->software_category_id)
                            ->ignore($projectMDM->id)
                    ],
                ]);


                DB::beginTransaction();

                $projectMDM->update($validated);

                DB::commit();

                return Helper::sendResponse($projectMDM, 'Project Of Master Data Management Form updated successfully');
            }
            return  Helper::sendError('Project Of Master Data Management Form not found', []);
        } catch (ValidationException $e) {
            DB::rollBack();
            return Helper::sendError($e->getMessage(), $e->errors(), HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return Helper::sendError('An error occurred while updating the MDM Project.', [], 500);
        }
    }

    public function show(int $id): JsonResponse
    {

        $projectMDM = ProjectMDM::with(['softwareCategory', 'mdmCategory'])->findOrFail($id);

        if ($projectMDM) {
            return Helper::sendResponse($projectMDM, 'Project Of Master Data Management Form retrieved successfully');
        }

        return Helper::sendError('Project Of Master Data Management Form not found', [], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $record = ProjectMDM::findOrFail($id);

            if (
                !MasterDataManagementForm::where('mdm_project_id', $record->id)->exists()
            ) {
                $record->delete();
                return Helper::sendResponse('', 'MDM Project deleted successfully');
            }

            return Helper::sendError("Cannot delete. This reference is in use.", [], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException $e) {
            return Helper::sendError('Record not found', [], HttpFoundationResponse::HTTP_NOT_FOUND); // Return 404 for not found

        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR); // Return 500 for general server errors
        }
    }

    public function getAllProjectMDMs()
    {
        try {
            $data = ProjectMDM::with('mdmCategory:id,name', 'softwareCategory:id,name')
                ->select(['id', 'name', 'mdm_category_id', 'software_category_id'])
                ->get();

            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Project Of Master Data Management Form not found.", 404);
        }
    }
    public function getRelatedProjectMDMs(Request $request)
    {
        try {
            $validated = $request->validate([
                'software_category_id' => 'required|int',
                'mdm_category_id' => 'required|int',
            ]);

            $data = ProjectMDM::with('mdmCategory:id,name', 'softwareCategory:id,name')
                ->select(['id', 'name', 'mdm_category_id', 'software_category_id'])
                ->where('software_category_id', $validated['software_category_id'])
                ->where('mdm_category_id', $validated['mdm_category_id'])
                ->get();

            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Project Of Master Data Management Form not found.", 404);
        }
    }
}
