<?php

namespace App\Http\Controllers\Forms;

use App\Enums\FormEnum;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\QaAssignmentRequest;
use App\Http\Requests\QualityAssuranceRequest;
use App\Http\Requests\StoreQualityAssuranceRequest;
use App\Http\Requests\StoreSCRFRequest;
use App\Http\Resources\QualityAssuranceResource;
use App\Models\QaAssignment;
use App\Models\Form;
use App\Models\Forms\QualityAssurance;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\User;
use App\Services\FormListService;
use App\Services\GlobalFormStoreService;
use App\Services\QualityAssuranceService;
use App\Traits\CommonControllerShowTrait;
use App\Traits\ModelDetails;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class QualityAssuranceController extends Controller
{
    use ModelDetails, CommonControllerShowTrait;
    protected $modelId;

    public function __construct()
    {
        $this->modelId = FormEnum::getIdByModelName(self::getModel());
        if (request()->is('api/*')) {
            $this->middleware('transaction.limit.check')->only(['store']);
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:QualityAssurance-view', ['only' => ['index', 'show']]);
        }
    }
    public function index(Request $request, FormListService $globalFormService)
    {
        try {
            $form = ['id' => $this->modelId, 'identity' => FormEnum::getModelById($this->modelId), 'tableName' => $this->getTableName()];
            $rules = [
                'sequence_no' => ['nullable', 'string', 'min:1'],
                'request_title' => ['nullable', 'string', 'min:1'],

                'comments' => ['nullable', 'string', 'min:1'],
                'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
                'sortBy' => ['nullable', 'string', 'max:20'],
                'sortOrder' => ['nullable', 'in:asc,desc']
            ];

            $validated = $request->validate($rules);
            $perPage = $request->input('perPage', 10);
            $sortBy = $validated['sortBy'] ?? 'id';
            $sortOrder = $validated['sortOrder'] ?? 'desc';
            $model = $this->getModel();
            $instance = new $model;

            if (!\Schema::hasColumn($instance->getTable(), $sortBy)) {
                return Helper::sendError('Invalid sorting column', [], Response::HTTP_BAD_REQUEST);
            }

            if (Auth::user()->hasRole('admin')) {
                return $this->getIndexResource()::collection($model::orderBy($sortBy, $sortOrder)->paginate($perPage));
            }

            $otherRelationships = $this->getCommonRelationships();
            $result = $globalFormService->getAll($form, $otherRelationships, $sortBy, $sortOrder, $perPage);

            return $result;
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function filters(Request $request, QualityAssuranceService $service)
    {
        return $service->filterRecord($request);
    }

    public function show($id)
    {
        try {
            $record = $this->getModel()::withoutGlobalScope(FormDataAccessScope::class)
                ->with(
                    $this->showCommonRelationships(),
                    'qaAssignment',
                    'attachables',
                    'qaAssignment.assurable:id,sequence_no,request_title,task_status'
                )->findOrFail($id);

            if ($record->qaAssignment && $record->qaAssignment->assurable && ($record->qaAssignment->assurable_type === 'App\\Models\\Forms\\SCRF' || $record->qaAssignment->assurable_type === 'App\\Models\\Forms\\MasterDataManagementForm')) {
                $record->qaAssignment->assurable->load('uatScenarios');
            }
            $resourceClass = $this->getResource();
            return new $resourceClass($record);
        } catch (ModelNotFoundException $e) {
            return Helper::sendError('Record not found.', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch record: ' . $e->getMessage(), [], 500);
        }
    }


    public function store(StoreQualityAssuranceRequest $request, GlobalFormStoreService $globalFormStoreService, QualityAssuranceService $dataService)
    {
        try {
            $response = '';
            $validatedData = $request->validated();
            $validatedData['request_form_id'] = (int) $validatedData['form_id'];
            $validatedData['form_id'] = $this->modelId;

            if ($request->save_as_draft === "false") {
                $result = $globalFormStoreService->workflowCheck($validatedData, $this->modelId);

                if (is_array($result)) {
                    $workflowId = $result['workflowId'];
                    $defined = $result['defined'];
                    $response = $dataService->storeQualityAssurance($validatedData, $workflowId, $defined, $this->modelId);
                    return $response;
                }
                return $result;
            }
            // elseif ($request->save_as_draft === "true") {
            //     $response = $dataService->draftQualityAssurance($validatedData, $this->modelId);
            // }

            return is_null($response) ? $validatedData : $response;
        } catch (\Exception $exception) {
            Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // public function store(StoreQualityAssuranceRequest $request, GlobalFormStoreService $globalFormStoreService, QualityAssuranceService $dataService)
    // {
    //     try {
    //         $validatedData = $request->validated();
    //         $result = $globalFormStoreService->workflowCheck($validatedData, $this->modelId);
    //         if (is_array($result)) {
    //             $workflowId = $result['workflowId'];
    //             $defined = $result['defined'];
    //             $response = $dataService->storeQualityAssurance($validatedData, $workflowId, $defined, $this->modelId);
    //             return $response;
    //         }
    //         return $result;
    //     } catch (\Exception $exception) {
    //         Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }
    // }

    public function statusQualityAssurance(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:quality_assurances,id',
                'status' => 'required|in:Ok,Modification Required',
                'feedback' => 'required|string|max:1000',
            ]);

            // Validate if the user is assigned to the QualityAssurance
            if (
                !DB::table('quality_assurance_user')
                    ->where('qa_user_id', Auth::user()->id)
                    ->where('quality_assurance_id', $validated['id'])
                    ->exists()
            ) {
                return Helper::sendError('Not found', [], Response::HTTP_NOT_FOUND);
            }

            $qualityAssurance = QualityAssurance::findOrFail($validated['id']);

            $result = $qualityAssurance->assignedToUsers()->where('qa_user_id', Auth::user()->id)->update([
                'status' => $validated['status'],
                'feedback' => $validated['feedback'],
                'status_at' => Carbon::now()
            ]);
            return Helper::sendResponse($result, 'Status updated successfully', Response::HTTP_OK);
        } catch (\Exception $exception) {
            return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(QualityAssurance $qualityAssurance)
    {
        // Detach the assigned users from the Quality Assurance form
        $qualityAssurance->users()->detach();

        // Delete the Quality Assurance form
        $qualityAssurance->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    public function getUsersForAssignment()
    {
        // Get the users available for assignment
        $users = User::all();

        return response()->json($users);
    }
}
