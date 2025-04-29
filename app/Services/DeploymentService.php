<?php

namespace App\Services;


use App\Http\Resources\DeploymentResource;
use App\Models\ApprovalStatus;
use App\Models\Forms\Deployment;
use App\Models\FormDependencies\UatScenario;
use App\Models\FormDependencies\DeploymentDetail;
use App\Services\Core\ProcessApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Helpers\Helper;
use App\Models\Attachment;
use Auth;

class DeploymentService extends BaseIndexService
{
    protected $model;
    protected $resourceClass;

    protected $processApprovalService;

    public function __construct()
    {
        $this->model = Deployment::class;
        $this->resourceClass = DeploymentResource::class;
        $this->processApprovalService = new ProcessApprovalService();
        parent::__construct($this->model, $this->resourceClass);
    }

    protected function getFilters()
    {
        return [
            'sequence_no',
            'request_title',
            'change_priority',
            // 'man_hours',
            // 'process_efficiency',
            // 'controls_improved',
            // 'cost_saved',
            // 'legal_reasons',
            // 'change_significance',
            // 'other_benefits',
            'reference_form_id',
            'reference_details',
        ];
    }

    protected function getRelationships()
    {
        return [
            'software_category' => 'name',
            'software_subcategories' => 'name',
        ];
    }
    public function storeService(array $storeData, $workflowId, $defined, $formId)
    {
        // $change_significance = ucfirst($storeData['change_significance']);
        $ddsData = $storeData['document_details'] ?? [];
        DB::beginTransaction();

        $record = $this->model::create([
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'reference_form_id' => $storeData['reference_form_id'],
            'reference_details' => $storeData['reference_details'],
            'change_priority' => $storeData['change_priority'],


            'location_id' => $storeData['location_id'],
            'project_id' => $storeData['project_id'],
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => $workflowId,
            'created_by' => Auth::user()->id,
            'draft_at' => null,
            'status' => 'Pending'
        ]);
        if (!empty($storeData['software_subcategory_id'])) {
            $record->software_subcategories()->attach($storeData['software_subcategory_id']);
        }
        foreach ($ddsData as $ddData) {
            $ddet = new DeploymentDetail([
                'deployment_id' => $record->id,
                'document_no' => $ddData['document_no'],
                'detail' => $ddData['detail']
            ]);
            $record->deploymentDetail()->save($ddet);
        }

        if (!empty($storeData['attachments'])) {
            $attachments = new AttachmentService();
            $attachments = $attachments->storeAttachment($storeData['attachments'], $record->id, $this->model);

            $record->attachments()->createMany($attachments);
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
        // $change_significance = ucfirst($storeData['change_significance']);
        $ddsData = $storeData['document_details'] ?? [];
        $array = [
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'change_priority' => $storeData['change_priority'],
            'reference_form_id' => $storeData['reference_form_id'],
            'reference_details' => $storeData['reference_details'],
            // 'request_specs' => $storeData['request_specs'],
            // 'change_type' => $storeData['change_type'],
            // 'man_hours' => $storeData['man_hours'],
            // 'process_efficiency' => $storeData['process_efficiency'],
            // 'controls_improved' => $storeData['controls_improved'],
            // 'cost_saved' => $storeData['cost_saved'],
            // 'legal_reasons' => $storeData['legal_reasons'],
            // 'change_significance' => $change_significance,
            // 'other_benefits' => $storeData['other_benefits'],
            'location_id' => $storeData['location_id'],
            'project_id' => $storeData['project_id'],
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => null,
            // 'software_category_id' => $storeData['software_category_id'],
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

        foreach ($ddsData as $ddData) {
            $ddet = new DeploymentDetail([
                'deployment_id' => $record->id,
                'document_no' => $ddData['document_no'],
                'detail' => $ddData['detail']
            ]);
            $record->deploymentDetail()->save($ddet);
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
        // $change_significance = ucfirst($updateData['change_significance']);
        $ddsData = $updateData['document_details'] ?? [];

        $record->update([
            'request_title' => $updateData['request_title'],
            // 'request_specs' => $updateData['request_specs'],
            'change_priority' => $updateData['change_priority'],
            'reference_form_id' => $updateData['reference_form_id'],
            'reference_details' => $updateData['reference_details'],
            // 'change_type' => $updateData['change_type'],
            // 'man_hours' => $updateData['man_hours'] ?? 0,
            // 'process_efficiency' => $updateData['process_efficiency'],
            // 'controls_improved' => $updateData['controls_improved'],
            // 'cost_saved' => $updateData['cost_saved'],
            // 'legal_reasons' => $updateData['legal_reasons'],
            // 'change_significance' => $change_significance,
            // 'other_benefits' => $updateData['other_benefits'],
            'location_id' => $updateData['location_id'],
            'project_id' => $updateData['project_id'],
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            // 'software_category_id' => $updateData['software_category_id'],
            'workflow_id' => $workflowId,
            'draft_at' => null,
            'status' => 'Pending'
        ]);

        // dd('hii');
        if (!empty($updateData['software_subcategory_id'])) {
            $record->software_subcategories()->sync($updateData['software_subcategory_id']);
        }

        $record->deploymentDetail()->delete();
        foreach ($ddsData as $ddData) {
            $ddet = new DeploymentDetail([
                'deployment_id' => $record->id,
                'document_no' => $ddData['document_no'],
                'detail' => $ddData['detail']
            ]);
            $record->deploymentDetail()->save($ddet);
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
        // $change_significance = ucfirst($updateData['change_significance']);
        $ddsData = $updateData['document_details'] ?? [];
        $record->update(
            [
                'request_title' => $updateData['request_title'],
                // 'request_specs' => $updateData['request_specs'],
                'change_priority' => $updateData['change_priority'],
                'reference_form_id' => $updateData['reference_form_id'],
                'reference_details' => $updateData['reference_details'],
                // 'change_type' => $updateData['change_type'],
                // 'man_hours' => $updateData['man_hours'],
                // 'process_efficiency' => $updateData['process_efficiency'],
                // 'controls_improved' => $updateData['controls_improved'],
                // 'cost_saved' => $updateData['cost_saved'],
                // 'legal_reasons' => $updateData['legal_reasons'],
                // 'change_significance' => $change_significance,
                // 'other_benefits' => $updateData['other_benefits'],
                'location_id' => $updateData['location_id'],
                'project_id' => $updateData['project_id'],
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

        $record->deploymentDetail()->delete();
        foreach ($ddsData as $ddData) {
            $ddet = new DeploymentDetail([
                 'deployment_id' => $record->id,
                'document_no' => $ddData['document_no'],
                'detail' => $ddData['detail']
            ]);
            $record->deploymentDetail()->save($ddet);
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

    // public static function updateSCRF(array $storeData, array $ddsData, $workflowId, $defined, $formId, SCRF $data)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $isDraft = strtolower($storeData['save_as_draft']);

    //         $change_significance = ucfirst($storeData['change_significance']);
    //         $wasReturn = false;
    //         if (is_null($data->draft_at) && $data->status == 'Return') {
    //             ApprovalStatus::where('reference_form_id', $formId)->where('key', $data->id)
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
    //             'change_significance' => $change_significance,
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
    //         foreach ($ddsData as $uatScenarioData) {
    //             $uatScenario = new DeploymentDetail([
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
    //             return Helper::sendResponse(new DeploymentResource($result), 'Successfully Added', 201);
    //         } elseif ($isDraft === "false" && $wasReturn === false) {
    //             $result = GlobalFormService::processApprovals($data, $defined, $workflowId, $formId);
    //             DB::commit();
    //             return Helper::sendResponse(new DeploymentResource($data), 'Successfully updated the record', 201);
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
            // 'software_category:id,name',
            // 'software_subcategories:id,name',
            'comments:id,comment',
        ]);

        $filters = [
            'sequence_no',
            'request_title',
            'change_priority',
            // 'request_specs',
            // 'change_type',
            // 'man_hours',
            // 'process_efficiency',
            // 'controls_improved',
            // 'cost_saved',
            // 'legal_reasons',
            // 'change_significance',
            // 'other_benefits',
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
