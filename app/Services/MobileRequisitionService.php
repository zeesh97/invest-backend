<?php

namespace App\Services;

use App\Http\Resources\MobileRequisitionResource;
use App\Http\Resources\StoreMobileRequisitionResource;
use App\Models\ApprovalStatus;
use App\Models\Forms\MobileRequisition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\Helper;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Storage;

class MobileRequisitionService extends BaseIndexService
{
    protected $model;
    protected $resourceClass;

    public function __construct()
    {
        $this->model = MobileRequisition::class;
        $this->resourceClass = MobileRequisitionResource::class;
        parent::__construct($this->model, $this->resourceClass);
    }
    // public function storeService(array $storeData, $workflowId, $defined, $formId)
    // {
    //     try {
    //         // DB::beginTransaction();
    //         $isDraft = strtolower($storeData['save_as_draft']);

    //         $globalFormService = new GlobalFormService();
    //         $sequenceNumber = $globalFormService->generateReferenceNumber(MobileRequisition::class);
    //         $user = User::find($storeData['request_for_user_id'])->only(['name', 'id', 'employee_no']);
    //         if(null == Auth::user()->location_id)
    //         {
    //             return Helper::sendError('Location does not exist. Please define location in your profile first.', [], 433);
    //         }

    //         $request_title = $user['name'] . ' - ' . $user['employee_no'];
    //         $mobileRequisition = MobileRequisition::create([
    //             'sequence_no' => $sequenceNumber,
    //             'request_title' => $request_title,
    //             'location_id' => Auth::user()->location_id,
    //             'department_id' => Auth::user()->department_id,
    //             'designation_id' => Auth::user()->designation_id,
    //             'section_id' => Auth::user()->section_id,
    //             'workflow_id' => $workflowId,
    //             'created_by' => Auth::user()->id,
    //             'request_for_user_id' => $storeData['request_for_user_id'],
    //             'issue_date' => $storeData['issue_date'] ?? null,
    //             'recieve_date' => $storeData['recieve_date'] ?? null,
    //             'make' => $storeData['make'] ?? null,
    //             'model' => $storeData['model'] ?? null,
    //             'imei' => $storeData['imei'] ?? null,
    //             'mobile_number' => $storeData['mobile_number'] ?? null,
    //             'remarks' => $storeData['remarks'],
    //             'draft_at' => ($isDraft === 'false') ? null : Carbon::now(),
    //             'status' => ($isDraft === "true") ? 'Draft' : 'Pending'
    //         ]);
    //         dd($mobileRequisition);
    //         if ($isDraft === 'false') {
    //             $result = GlobalFormService::processApprovals($mobileRequisition, $defined, $workflowId, $formId);

    //             return Helper::sendResponse(new StoreMobileRequisitionResource($result), 'Successfully Added', 201);
    //         } else {
    //             return Helper::sendResponse($mobileRequisition, 'Successfully saved as a Draft.', 201);
    //         }

    //     } catch (\Exception $e) {
    //         \Log::error('Error in Mobile Requisition: ' . $e->getMessage());
    //         return Helper::sendError($e->getMessage(), [], 433);
    //     }
    // }
    public function storeService(array $storeData, $workflowId, $defined, $formId)
    {
        DB::beginTransaction();
        $user = User::find($storeData['request_for_user_id'])->only(['name', 'id', 'employee_no']);

        if (is_null((Auth::user()->location_id))) {
            return Helper::sendError('Location does not exist. Please define location in your profile first.', [], 433);
        }
        $request_title = $user['name'] . ' - ' . $user['employee_no'];
        $record = $this->model::create([
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $request_title,
            'request_for_user_id' => $storeData['request_for_user_id'],
            'issue_date' => $storeData['issue_date'] ?? null,
            'recieve_date' => $storeData['recieve_date'] ?? null,
            'make' => $storeData['make'] ?? null,
            'model' => $storeData['model'] ?? null,
            'imei' => $storeData['imei'] ?? null,
            'mobile_number' => $storeData['mobile_number'] ?? null,
            'remarks' => $storeData['remarks'],
            'location_id' => Auth::user()->location_id,
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => $workflowId,
            'created_by' => Auth::user()->id,
            'draft_at' => null,
            'status' => 'Pending'
        ]);


        $result = GlobalFormService::processApprovals($record, $defined, $workflowId, $formId);
        $notifiedUserIds = array_unique(array_merge(
            $result['approverIds'],
            // $result['subscriberIds'],
            $result['parallelApproverIds'],
            [$result['created_by']]
        ));
        DB::commit();
        return ['notifiedUserIds' => $notifiedUserIds, 'data' => $result['resultData']];
    }
    public function draftService(array $storeData)
    {
        $user = User::find($storeData['request_for_user_id'])->only(['name', 'id', 'employee_no']);

        if (is_null((Auth::user()->location_id))) {
            return Helper::sendError('Location does not exist. Please define location in your profile first.', [], 433);
        }
        $request_title = $user['name'] . ' - ' . $user['employee_no'];

        $array = [
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $request_title,
            'request_for_user_id' => $storeData['request_for_user_id'],
            'issue_date' => $storeData['issue_date'] ?? null,
            'recieve_date' => $storeData['recieve_date'] ?? null,
            'make' => $storeData['make'] ?? null,
            'model' => $storeData['model'] ?? null,
            'imei' => $storeData['imei'] ?? null,
            'mobile_number' => $storeData['mobile_number'] ?? null,
            'remarks' => $storeData['remarks'],
            'location_id' => Auth::user()->location_id,
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => null,
            'created_by' => Auth::user()->id,
            'draft_at' => Carbon::now(),
            'status' => 'Draft'
        ];
        DB::beginTransaction();
        $record = $this->model::updateOrCreate(
            $array
        );
        DB::commit();
        return $record;
    }

    public function updateService(array $updateData, $workflowId, $defined, $formId, $id)
    {
        $record = $this->model::findOrFail($id);
        DB::beginTransaction();
        if (ApprovalStatus::where('form_id', $formId)->where('key', $id)->exists()) {
            ApprovalStatus::where('form_id', $formId)->where('key', $id)->delete();
        }

        $user = User::find($updateData['request_for_user_id'])->only(['name', 'id', 'employee_no']);

        if (is_null((Auth::user()->location_id))) {
            return Helper::sendError('Location does not exist. Please define location in your profile first.', [], 433);
        }
        $request_title = $user['name'] . ' - ' . $user['employee_no'];

        $record->update([
            'request_title' => $request_title,
            'location_id' => Auth::user()->location_id,
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => $workflowId,
            'draft_at' => null,
            'status' => 'Pending'
        ]);

        $result = GlobalFormService::processApprovals($record, $defined, $workflowId, $formId);
        DB::commit();
        $notifiedUserIds = array_unique(array_merge(
            $result['approverIds'],
            // $result['subscriberIds'],
            $result['parallelApproverIds'],
            [$result['created_by']]
        ));

        return ['notifiedUserIds' => $notifiedUserIds, 'data' => $result['resultData']];
    }

    public function draftUpdateService(array $updateData, $formId, $id)
    {
        $record = $this->model::findOrFail($id);
        if (ApprovalStatus::where('form_id', $formId)->where('key', $id)->exists()) {
            ApprovalStatus::where('form_id', $formId)->where('key', $id)->delete();
        }
        $record->update(
            [
                'request_for_user_id' => $updateData['request_for_user_id'],
                'issue_date' => $updateData['issue_date'] ?? null,
                'recieve_date' => $updateData['recieve_date'] ?? null,
                'make' => $updateData['make'] ?? null,
                'model' => $updateData['model'] ?? null,
                'imei' => $updateData['imei'] ?? null,
                'mobile_number' => $updateData['mobile_number'] ?? null,
                'remarks' => $updateData['remarks'],
                'location_id' => Auth::user()->location_id,
                'department_id' => Auth::user()->department_id,
                'designation_id' => Auth::user()->designation_id,
                'section_id' => Auth::user()->section_id,
                'workflow_id' => null,
                'draft_at' => Carbon::now(),
                'status' => 'Draft'
            ]
        );
        return $record;
    }

    public function filterRecord(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $mobileRequisition = MobileRequisition::with([
            'location:id,name',
            'request_for_user:id,name',
        ]);

        $filters = [
            'sequence_no',
            'request_title',
        ];

        foreach ($filters as $filter) {
            $value = $request->$filter;

            if ($value) {
                $mobileRequisition->where($filter, 'LIKE', '%' . $value . '%');
            }
        }

        $relationships = [
            'location' => 'name',
            'request_for_user' => 'name',
        ];

        foreach ($relationships as $relationship => $column) {
            $value = $request->$relationship;

            if ($value) {
                $mobileRequisition->whereHas($relationship, function ($query) use ($column, $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                });
            }
        }

        return MobileRequisitionResource::collection($mobileRequisition->latest()->paginate($perPage));
    }
}
