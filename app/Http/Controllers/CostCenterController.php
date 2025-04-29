<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\CostCenterResource;
use App\Models\CostCenter;
use App\Models\Forms\CRF;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CostCenterController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:CostCenter-view|CostCenter-create|CostCenter-edit|CostCenter-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:CostCenter-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:CostCenter-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:CostCenter-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $costCenters = CostCenter::latest()->select(['id', 'name', 'description'])->get();
                return Helper::sendResponse($costCenters, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $costCenters = CostCenter::with('department:id,name', 'location:id,name')
                    ->select('id', 'cost_center', 'description', 'project', 'department_id', 'location_id')
                    ->latest()
                    ->paginate($perPage);
                return Helper::sendResponse($costCenters, 'Success', 200);
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
            $validated = $request->validate(
                [
                    'description' => 'sometimes|max:250',
                    'department_id' => 'required|exists:departments,id',
                    'location_id' => 'required|exists:locations,id',
                    'cost_center' => 'required',
                    'string',
                    'min:2',
                    'max:255',
                    'project' => [
                        'required',
                        'string',
                        'min:2',
                        'max:255',
                        Rule::unique('cost_centers')->where(function ($query) use ($request) {
                            return $query->where('department_id', $request->department_id)
                                ->where('project', $request->project)
                                ->where('location_id', $request->location_id);
                        }),
                    ],
                ],
                [
                    'project.unique' => 'The combination of department, location, and project must be unique for this cost center.',
                ]
            );
            DB::beginTransaction();
            $costCenter = CostCenter::create($validated);
            DB::commit();
            return Helper::sendResponse($costCenter, 'Cost Center created successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            return Helper::sendError($e->getMessage(), $e->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return Helper::sendError('An error occurred while creating the cost center.', [], 500); // Use 500 for internal server errors
        }
    }

    /**
     * Display the specified resource.
     */
    // public function show(CostCenter $costCenter): Response
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CostCenter $costCenter)
    {
        try {
            $validated = $request->validate([
                'description' => 'sometimes|max:250',
                'cost_center' => 'sometimes|string|min:2|max:255',
                'department_id' => 'sometimes|exists:departments,id',
                'location_id' => 'sometimes|exists:locations,id',
                'project' => [
                    'sometimes',
                    'string',
                    'min:2',
                    'max:255',
                    Rule::unique('cost_centers')->where(function ($query) use ($request, $costCenter) {
                        return $query->where('department_id', $request->department_id)
                            ->where('project', $request->project)
                            ->where('location_id', $request->location_id)
                            ->where('id', '!=', $costCenter->id); // Exclude the current cost center from the uniqueness check
                    }),
                ],
            ], [
                'project.unique' => 'The combination of department, location, and project must be unique for this cost center.',
            ]);

            DB::beginTransaction();

            $costCenter->update($validated);

            DB::commit();

            return Helper::sendResponse($costCenter, 'Cost Center updated successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            return Helper::sendError($e->getMessage(), $e->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return Helper::sendError('An error occurred while updating the cost center.', [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CostCenter $costCenter): JsonResponse
    {
        try {
            if (
                !CRF::where('cost_center_id', $costCenter->id)->exists()
            ) {
                $costCenter->delete();
                return Helper::sendResponse('', 'Cost center deleted successfully');
            }
            return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
