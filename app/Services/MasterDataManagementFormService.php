<?php

namespace App\Services;


use App\Http\Resources\MasterDataManagementFormResource;
use App\Models\ApprovalStatus;
use App\Models\Forms\MasterDataManagementForm;
use App\Services\Core\ProcessApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Helpers\Helper;
use App\Models\Attachment;
use App\Models\FormDependencies\UatScenarioMDM;
use Auth;

class MasterDataManagementFormService extends BaseIndexService
{
    protected $model;
    protected $resourceClass;

    protected $processApprovalService;

    public function __construct()
    {
        $this->model = MasterDataManagementForm::class;
        $this->resourceClass = MasterDataManagementFormResource::class;
        $this->processApprovalService = new ProcessApprovalService();
        parent::__construct($this->model, $this->resourceClass);
    }

    protected function getFilters()
    {
        return [
            'sequence_no',
            'request_title',
            'request_specs',
            'change_priority',
        ];
    }

    protected function getRelationships()
    {
        return [
            'software_category' => 'name',
            'software_subcategories' => 'name',
        ];
    }
    public function storeService(array $storeData, $workflowId = null, $defined = null, $formId)
    {
        $uatScenariosData = $storeData['uat_scenarios'] ?? [];
        DB::beginTransaction();

        $record = $this->model::create([
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'request_specs' => $storeData['request_specs'],
            'mdm_project_id' => $storeData['mdm_project_id'],
            'change_priority' => $storeData['change_priority'],
            'location_id' => $storeData['location_id'],
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => $workflowId,
            'software_category_id' => $storeData['software_category_id'],
            'mdm_category_id' => $storeData['mdm_category_id'],
            'created_by' => Auth::user()->id,
            'draft_at' => null,
            'status' => 'Pending'
        ]);
        if (!empty($storeData['software_subcategory_id'])) {
            $record->software_subcategories()->attach($storeData['software_subcategory_id']);
        }

        foreach ($uatScenariosData as $uatScenarioData) {
            if (!is_null($uatScenarioData['status']) && !is_null($uatScenarioData['detail'])) {
                $uatScenario = new UatScenarioMDM([
                    'master_data_management_form_id' => $record->id,
                    'detail' => $uatScenarioData['detail'],
                    'status' => $uatScenarioData['status']
                ]);
                $record->uatScenarios()->save($uatScenario);
            }
        }

        if (!empty($storeData['attachments'])) {
            $attachments = new AttachmentService();
            $attachments = $attachments->storeAttachment($storeData['attachments'], $record->id, $this->model);

            $record->attachments()->createMany($attachments);
        }

        if (is_null($defined)) {

            DB::commit();
            return ['notifiedUserIds' => [], 'data' => []];
        }

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
        $uatScenariosData = $storeData['uat_scenarios'] ?? [];
        $array = [
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'request_specs' => $storeData['request_specs'],
            'mdm_project_id' => $storeData['mdm_project_id'],
            'change_priority' => $storeData['change_priority'],
            'location_id' => $storeData['location_id'],
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => null,
            'software_category_id' => $storeData['software_category_id'],
            'mdm_category_id' => $storeData['mdm_category_id'],
            'created_by' => Auth::user()->id,
            'draft_at' => Carbon::now(),
            'status' => 'Draft'
        ];
        DB::beginTransaction();
        $record = $this->model::updateOrCreate(
            $array
        );
        if (!empty($storeData['software_subcategory_id'])) {
            $record->software_subcategories()->attach($storeData['software_subcategory_id']);
        }

        foreach ($uatScenariosData as $uatScenarioData) {
            if (!is_null($uatScenarioData['status']) && !is_null($uatScenarioData['detail'])) {
                $uatScenario = new UatScenarioMDM([
                    'master_data_management_form_id' => $record->id,
                    'detail' => $uatScenarioData['detail'],
                    'status' => $uatScenarioData['status']
                ]);
                $record->uatScenarios()->save($uatScenario);
            }
        }

        if (!empty($storeData['attachments'])) {
            $attachments = new AttachmentService();
            $attachments = $attachments->storeAttachment($storeData['attachments'], $record->id, $this->model);

            $record->attachments()->createMany($attachments);
        }

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
        $uatScenariosData = $updateData['uat_scenarios'] ?? [];

        $record->update([
            'request_title' => $updateData['request_title'],
            'request_specs' => $updateData['request_specs'],
            'mdm_project_id' => $updateData['mdm_project_id'],
            'change_priority' => $updateData['change_priority'],
            'location_id' => $updateData['location_id'],
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'software_category_id' => $updateData['software_category_id'],
            'mdm_category_id' => $updateData['mdm_category_id'],
            'workflow_id' => $workflowId,
            'draft_at' => null,
            'status' => 'Pending'
        ]);

        if (!empty($updateData['software_subcategory_id'])) {
            $record->software_subcategories()->sync($updateData['software_subcategory_id']);
        }

        $record->uatScenarios()->delete();
        foreach ($uatScenariosData as $uatScenarioData) {
            if (!is_null($uatScenarioData['status']) && !is_null($uatScenarioData['detail'])) {
                $uatScenario = new UatScenarioMDM([
                    'master_data_management_form_id' => $record->id,
                    'detail' => $uatScenarioData['detail'],
                    'status' => $uatScenarioData['status']
                ]);
                $record->uatScenarios()->save($uatScenario);
            }
        }

        if (!empty($updateData['deleted_attachments'])) {
            foreach ($updateData['deleted_attachments'] as $attachmentId) {
                $attachment = $record->attachments()->find($attachmentId);
                if ($attachment) {
                    $attachmentPath = str_replace('uploads/', '', $attachment->filename);
                    Storage::disk('public')->delete($attachmentPath);
                    $attachment->delete();
                }
            }
        }

        if (!empty($updateData['attachments'])) {

            $attachments = new AttachmentService();
            $newAttachmentData = $attachments->storeAttachment($updateData['attachments'], $record->id, $this->model);
            $record->attachments()->createMany($newAttachmentData);
        }

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
        $uatScenariosData = $updateData['uat_scenarios'] ?? [];
        $record->update(
            [
                'request_title' => $updateData['request_title'],
                'request_specs' => $updateData['request_specs'],
                'mdm_project_id' => $updateData['mdm_project_id'],
                'change_priority' => $updateData['change_priority'],
                'software_category_id' => $updateData['software_category_id'],
                'mdm_category_id' => $updateData['mdm_category_id'],
                'location_id' => $updateData['location_id'],
                'department_id' => Auth::user()->department_id,
                'designation_id' => Auth::user()->designation_id,
                'section_id' => Auth::user()->section_id,
                'workflow_id' => null,
                'draft_at' => Carbon::now(),
                'status' => 'Draft'
            ]
        );

        if (!empty($updateData['software_subcategory_id'])) {
            $record->software_subcategories()->sync($updateData['software_subcategory_id']);
        }

        $record->uatScenarios()->delete();
        foreach ($uatScenariosData as $uatScenarioData) {
            if (!is_null($uatScenarioData['status']) && !is_null($uatScenarioData['detail'])) {
                $uatScenario = new UatScenarioMDM([
                    'master_data_management_form_id' => $record->id,
                    'detail' => $uatScenarioData['detail'],
                    'status' => $uatScenarioData['status']
                ]);
                $record->uatScenarios()->save($uatScenario);
            }
        }

        if (!empty($updateData['deleted_attachments'])) {
            foreach ($updateData['deleted_attachments'] as $attachmentId) {
                $attachment = $record->attachments()->find($attachmentId);
                if ($attachment) {
                    $attachmentPath = str_replace('uploads/', '', $attachment->filename);
                    Storage::disk('public')->delete($attachmentPath);
                    $attachment->delete();
                }
            }
        }

        if (!empty($updateData['attachments'])) {

            $attachments = new AttachmentService();
            $newAttachmentData = $attachments->storeAttachment($updateData['attachments'], $record->id, $this->model);
            $record->attachments()->createMany($newAttachmentData);
        }
        return $record;
    }

    // public static function updateMasterDataManagementForm(array $storeData, array $uatScenariosData, $workflowId, $defined, $formId, MasterDataManagementForm $data)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $isDraft = strtolower($storeData['save_as_draft']);

    //         $wasReturn = false;
    //         if (is_null($data->draft_at) && $data->status == 'Return') {
    //             ApprovalStatus::where('form_id', $formId)->where('key', $data->id)
    //                 ->delete();
    //             $wasReturn = true;
    //         }

    //         $data->update([
    //             'request_title' => $storeData['request_title'],
    //             'request_specs' => $storeData['request_specs'],
    //             'change_type' => $storeData['change_type'],
    //             'change_priority' => $storeData['change_priority'],
    //             'man_hours' => $storeData['man_hours'],
    //             'process_efficiency' => $storeData['process_efficiency'],
    //             'controls_improved' => $storeData['controls_improved'],
    //             'cost_saved' => $storeData['cost_saved'],
    //             'legal_reasons' => $storeData['legal_reasons'],
    //             'other_benefits' => $storeData['other_benefits'],
    //             'workflow_id' => $workflowId,
    //             'location_id' => $storeData['location_id'],
    //             'software_category_id' => $storeData['software_category_id'],
    //             'updated_by' => Auth::user()->id,
    //             'draft_at' => ($isDraft === "false") ? null : Carbon::now(),
    //             // 'status' => ($data->status == 'Approved') ? $data->status : 'Pending',
    //             'status' => ($isDraft === "true") ? 'Draft' : 'Pending'
    //         ]);

    //         if (!empty($storeData['software_subcategory_id'])) {
    //             $data->software_subcategories()->sync($storeData['software_subcategory_id']);
    //         }
    //         $data->uatScenarios()->delete();
    //         foreach ($uatScenariosData as $uatScenarioData) {
    //             $uatScenario = new UatScenarioMDM([
    //                 'record_id' => $data->id,
    //                 'detail' => $uatScenarioData['detail'],
    //                 'status' => $uatScenarioData['status']
    //             ]);
    //             $data->uatScenarios()->save($uatScenario);
    //         }
    //         if (!empty($storeData['attachments'])) {
    //             $attachments = new AttachmentService();
    //             $attachments = $attachments->storeAttachment($storeData['attachments'], $data->id, $this->model);

    //             $data->attachments()->delete();
    //             $data->attachments()->createMany($attachments);
    //         }
    //         // if (!empty($storeData['attachments'])) {
    //         //     $attachments = new AttachmentService();
    //         //     $attachments = $attachments->storeAttachment($storeData['attachments'], $data->id, $this->model);
    //         //     $data->attachments()->createMany($attachments);
    //         // }
    //         if ($isDraft === "false" && $wasReturn === true) {
    //             $result = GlobalFormService::processApprovals($data, $defined, $workflowId, $formId);

    //             DB::commit();
    //             return Helper::sendResponse(new MasterDataManagementFormResource($result), 'Successfully Added', 201);
    //         } elseif ($isDraft === "false" && $wasReturn === false) {
    //             $result = GlobalFormService::processApprovals($data, $defined, $workflowId, $formId);
    //             DB::commit();
    //             return Helper::sendResponse(new MasterDataManagementFormResource($data), 'Successfully updated the record', 201);
    //         } else {
    //             DB::commit();
    //             return Helper::sendResponse($data, 'Successfully saved as a Draft.', 201);
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Error in Service: ' . $e->getMessage());
    //         return Helper::sendError($e->getMessage(), [], 433);
    //     }
    // }


    public function filterRecord(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $record = $this->model::with([
            'software_category:id,name',
            'software_subcategories:id,name',
            'comments:id,comment',
            'projectMDM',
        ]);

        $filters = [
            'sequence_no',
            'request_title',
            'request_specs',
            'mdm_project_id',
            'change_type',
            'change_priority',
            'man_hours',
            'process_efficiency',
            'controls_improved',
            'cost_saved',
            'legal_reasons',
            'other_benefits',
        ];

        foreach ($filters as $filter) {
            $value = $request->$filter;

            if ($value) {
                $record->where($filter, 'LIKE', '%' . $value . '%');
            }
        }

        $relationships = [
            // 'workflow' => 'name',
            'software_category' => 'name',
            'software_subcategories' => 'name',
            'comments' => 'comment',
        ];

        foreach ($relationships as $relationship => $column) {
            $value = $request->$relationship;

            // if ($value) {
            //     $record->whereHas($relationship, function ($query) use ($column, $value) {
            //         $query->where($column, 'LIKE', '%' . $value . '%');
            //     });
            // }
            if ($value) {
                // Use whereRelation to filter on the relationship's attribute
                $record->whereRelation($relationship, $column, 'LIKE', '%' . $value . '%');
            }
        }

        $sortBy = $request->query('sortBy', 'created_at');
        $sortOrder = $request->query('sortOrder', 'desc');

        if (in_array($sortBy, array_merge($filters, ['created_at', 'updated_at'])) && in_array($sortOrder, ['asc', 'desc'])) {
            $record->orderBy($sortBy, $sortOrder);
        } else {
            $record->latest();
        }

        return $this->resourceClass::collection($record->paginate($perPage));
    }
}
