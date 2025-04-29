<?php

namespace App\Http\Controllers\Forms;

use App\Enums\FormEnum;
use App\Events\AssignPermissionToUsers;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\StoreSapAccessFormRequest;
use App\Http\Requests\UpdateSapAccessFormRequest;
use App\Services\FormListService;
use App\Services\GlobalFormStoreService;
use App\Services\SapAccessFormService;
use App\Services\GlobalFormService;
use App\Traits\CommonControllerShowTrait;
use App\Traits\ModelDetails;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SapAccessFormController extends Controller
{
    use ModelDetails, CommonControllerShowTrait;
    protected $modelId;

    public function __construct()
    {
        $this->modelId = FormEnum::getIdByModelName(self::getModel());
        if (request()->is('api/*')) {
            $this->middleware('transaction.limit.check')->only(['store']);
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:SapAccessForm-view', ['only' => ['index', 'show']]);
        }
    }

    public function index(Request $request, FormListService $globalFormService)
    {
        try {
            $form = ['id' => $this->modelId, 'identity' => FormEnum::getModelById($this->modelId), 'tableName' => $this->getTableName()];
            $rules = [
                'sequence_no' => ['nullable', 'string', 'min:1'],
                'request_title' => ['nullable', 'string', 'min:1'],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
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
            $otherRelationships = $this->getCommonRelationships();
            array_push(
                $otherRelationships,
                'company:id,name',
            );

            if (Auth::user()->hasRole('admin')) {
                return $this->getIndexResource()::collection($model::with($otherRelationships)->orderBy($sortBy, $sortOrder)->paginate($perPage));
            }
            $result = $globalFormService->getAll($form, $otherRelationships, $sortBy, $sortOrder, $perPage);

            return $result;
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function filters(Request $request, SapAccessFormService $service)
    {
        return $service->filterRecord($request);
    }

    public function show($id)
    {
        try {
            $record = $this->getModel()::with(
                $this->getCommonRelationships(),
                'company:id,name',
            )->findOrFail($id);
            // dd($record);
            $resourceClass = $this->getResource();
            return new $resourceClass($record);
        } catch (ModelNotFoundException $e) {
            return Helper::sendError('Record not found.', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch record: ' . $e->getMessage(), [], 500);
        }
    }


    public function store(StoreSapAccessFormRequest $request, GlobalFormStoreService $globalFormStoreService, SapAccessFormService $service)
    {
        try {
            $response = '';
            $validatedData = $request->validated();
            $globalFormService = new GlobalFormService();
            $sequenceNumber = $globalFormService->generateReferenceNumber($this->getModel());
            $validatedData['sequence_no'] = $sequenceNumber;
            $validatedData['form_id'] = $this->modelId;

            if ($request->save_as_draft === "false") {
                $workflowResult = $globalFormStoreService->workflowCheck($validatedData, $this->modelId);

                if (is_array($workflowResult)) {
                    $workflowId = $workflowResult['workflowId'];
                    $defined = $workflowResult['defined'];

                    try {
                        $response = $service->storeService($validatedData, $workflowId, $defined, $this->modelId);

                        event(new AssignPermissionToUsers($response['notifiedUserIds'], $this->getModel(), $response['data']['id']));
                        $resourceClass = $this->getResource();
                        return Helper::sendResponse(new $resourceClass($response['data']), 'Successfully Added', 201);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                }
                return $workflowResult;
            } elseif ($request->save_as_draft === "true") {

                try {
                    $response = $service->draftService($validatedData);
                    event(new AssignPermissionToUsers([$response->created_by], $this->getModel(), $response->id));
                    return Helper::sendResponse($response, 'Saved as Draft', 201);
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch (\Exception $exception) {
            return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function update(UpdateSapAccessFormRequest $request, GlobalFormStoreService $globalFormStoreService, SapAccessFormService $service, $id)
    {
        try {
            $response = '';
            $validatedData = $request->validated();
            $validatedData['form_id'] = $this->modelId;

            if ($request->save_as_draft === 'false') {
                $model = FormEnum::getModelById($this->modelId);
                $record = $model::with('company')->findOrFail($id);
                if ($record->status === 'Pending') {
                    // dd($validatedData);
                    try {
                        $response = $service->updateByEditorService($validatedData, $record, $this->modelId);
                        // event(new AssignPermissionToUsers($response['notifiedUserIds'], $this->getModel(), $response['data']['id']));
                        return Helper::sendResponse([], 'Successfully Updated', 201);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                }
                $workflowResult = $globalFormStoreService->workflowCheck($validatedData, $this->modelId);

                if (is_array($workflowResult)) {
                    $workflowId = $workflowResult['workflowId'];
                    $defined = $workflowResult['defined'];

                    try {
                        $response = $service->updateService($validatedData, $workflowId, $defined, $this->modelId, $id);

                        event(new AssignPermissionToUsers($response['notifiedUserIds'], $this->getModel(), $response['data']['id']));
                        $resourceClass = $this->getResource();
                        return Helper::sendResponse(new $resourceClass($response['data']), 'Successfully Added', 201);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                }
                return $workflowResult;
            } elseif ($request->save_as_draft === "true") {

                try {
                    $response = $service->draftUpdateService($validatedData, $this->modelId, $id);
                    return Helper::sendResponse($response, 'Saved as Draft', 201);
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch (\Exception $exception) {
            return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = $this->getModel()::findOrFail($id);
            if ($data->draft_at == null) {
                return Helper::sendError('Cannot process this action.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data->attachables()->delete();

            $data->equipmentRequests()->delete();
            $data->softwareRequests()->delete();
            $data->serviceRequests()->delete();

            $data->delete();
            DB::commit();
            return Helper::sendResponse([], 'Record deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in Service: ' . $e->getMessage());
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }
}
