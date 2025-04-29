<?php

namespace App\Services;

use App\Enums\FormEnum;
use App\Http\Resources\SapAccessFormResource;
use App\Http\Resources\StoreSapAccessFormResource;
use App\Http\Resources\UpdateSapAccessFormResource;
use App\Models\ApprovalStatus;
use App\Models\CostCenter;
use App\Models\Forms\SapAccessForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Helpers\Helper;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SapAccessFormService extends BaseIndexService
{
    protected $model;
    protected $resourceClass;

    public function __construct()
    {
        $this->model = SapAccessForm::class;
        $this->resourceClass = SapAccessFormResource::class;
        parent::__construct($this->model, $this->resourceClass);
    }

    protected function getFilters()
    {
        return [
            'sequence_no',
            'request_title',
            'status'
        ];
    }

    protected function getRelationships()
    {
        return [
            'company' => 'name',
        ];
    }
    public function storeService(array $storeData, $workflowId, $defined, $formId)
    {
        DB::beginTransaction();
        $data = [
            'location' => $storeData['location'] ?? [],
            'plant' => $storeData['plant'] ?? [],
            'sales_distribution' => $storeData['sales_distribution'] ?? [],
            'material_management' => $storeData['material_management'] ?? [],
            'plant_maintenance' => $storeData['plant_maintenance'] ?? [],
            'financial_accounting_costing' => $storeData['financial_accounting_costing'] ?? [],
            'production_planning' => $storeData['production_planning'] ?? [],
            'human_resource' => $storeData['human_resource'] ?? [],
        ];

        $record = $this->model::create([
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'data' => $data,
            'type' => $storeData['type'],
            'roles_required' => $storeData['roles_required'],
            'tcode_required' => $storeData['tcode_required'],
            'business_justification' => $storeData['business_justification'],
            'company_id' => $storeData['company_id'],
            'sap_id' => $storeData['sap_id'],
            'location_id' => Auth::user()->location_id,
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => $workflowId,
            'created_by' => Auth::user()->id,
            'draft_at' => null,
            'status' => 'Pending'

        ]);

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
        $data = [
            'location' => $storeData['location'] ?? [],
            'plant' => $storeData['plant'] ?? [],
            'sales_distribution' => $storeData['sales_distribution'] ?? [],
            'material_management' => $storeData['material_management'] ?? [],
            'plant_maintenance' => $storeData['plant_maintenance'] ?? [],
            'financial_accounting_costing' => $storeData['financial_accounting_costing'] ?? [],
            'production_planning' => $storeData['production_planning'] ?? [],
            'human_resource' => $storeData['human_resource'] ?? [],
        ];

        $array = [
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'data' => $data,
            'type' => $storeData['type'],
            'roles_required' => $storeData['roles_required'],
            'tcode_required' => $storeData['tcode_required'],
            'business_justification' => $storeData['business_justification'],
            'company_id' => $storeData['company_id'],
            'sap_id' => $storeData['sap_id'],
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
        if (!empty($storeData['attachments'])) {
            $attachments = new AttachmentService();
            $attachments = $attachments->storeAttachment($storeData['attachments'], $record->id, $this->model);

            $record->attachments()->createMany($attachments);
        }
        DB::commit();
        return $record;
    }
    public function updateByEditorService(array $updateData, $record, $formId)
    {
        // dd($updateData['save_as_draft']);
        DB::beginTransaction();

        if ($updateData['save_as_draft'] !== 'false') {
            $check = ApprovalStatus::where('form_id', $formId)
                ->where('key', $record->id)
                ->where('user_id', Auth::user()->id)->first();
            if ($check && $check->condition_id == 9) {
                GlobalFormService::approvalConditions($record, $formId, $check->condition_id, $check->sequence_no, $check->approver_id);
            }
        }
        DB::commit();
        return $record;
    }

    public function updateService(array $updateData, $workflowId, $defined, $formId, $id)
    {
        $data = [
            'location' => $updateData['location'] ?? [],
            'plant' => $updateData['plant'] ?? [],
            'sales_distribution' => $updateData['sales_distribution'] ?? [],
            'material_management' => $updateData['material_management'] ?? [],
            'plant_maintenance' => $updateData['plant_maintenance'] ?? [],
            'financial_accounting_costing' => $updateData['financial_accounting_costing'] ?? [],
            'production_planning' => $updateData['production_planning'] ?? [],
            'human_resource' => $updateData['human_resource'] ?? [],
        ];

        $array = [
            'request_title' => $updateData['request_title'] ?? null,
            'data' => $data,
            'type' => $updateData['type'] ?? null,
            'roles_required' => $updateData['roles_required'] ?? null,
            'tcode_required' => $updateData['tcode_required'] ?? null,
            'business_justification' => $updateData['business_justification'] ?? null,
            'company_id' => $updateData['company_id'] ?? null,
            'sap_id' => $updateData['sap_id'] ?? null,
            'location_id' => Auth::user()->location_id,
            'department_id' => Auth::user()->department_id,
            'designation_id' => Auth::user()->designation_id,
            'section_id' => Auth::user()->section_id,
            'workflow_id' => $workflowId,
            'updated_by' => Auth::user()->id,
            'status' => 'Pending'
        ];

        $record = $this->model::findOrFail($id);
        DB::beginTransaction();
        if (ApprovalStatus::where('form_id', $formId)->where('key', $id)->exists()) {
            ApprovalStatus::where('form_id', $formId)->where('key', $id)->delete();
        }
        $record->update($array);

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
        $data = [
            'location' => $updateData['location'] ?? [],
            'plant' => $updateData['plant'] ?? [],
            'sales_distribution' => $updateData['sales_distribution'] ?? [],
            'material_management' => $updateData['material_management'] ?? [],
            'plant_maintenance' => $updateData['plant_maintenance'] ?? [],
            'financial_accounting_costing' => $updateData['financial_accounting_costing'] ?? [],
            'production_planning' => $updateData['production_planning'] ?? [],
            'human_resource' => $updateData['human_resource'] ?? [],
        ];

        $array = [
            'request_title' => $updateData['request_title'],
            'data' => $data,
            'type' => $updateData['type'],
            'roles_required' => $updateData['roles_required'],
            'tcode_required' => $updateData['tcode_required'],
            'business_justification' => $updateData['business_justification'],
            'company_id' => $updateData['company_id'],
            'sap_id' => $updateData['sap_id'],
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
        $record = $record->update(
            $array
        );
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
        DB::commit();
        return $record;
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = SapAccessForm::findOrFail($id);
            if ($data->draft_at == null) {
                return Helper::sendError('Cannot process this action.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data->delete();
            DB::commit();
            return Helper::sendResponse([], 'SapAccessForm deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in SapAccessForm Service: ' . $e->getMessage());
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }

    public function filterRecord(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $record = SapAccessForm::with([
            'company:id,name',
        ]);

        $filters = [
            'sequence_no',
            'request_title',
        ];

        foreach ($filters as $filter) {
            $value = $request->$filter;

            if ($value) {
                $record->where($filter, 'LIKE', '%' . $value . '%');
            }
        }

        $relationships = [
            'company' => 'name',
        ];

        foreach ($relationships as $relationship => $column) {
            $value = $request->$relationship;

            if ($value) {
                $record->whereHas($relationship, function ($query) use ($column, $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                });
            }
        }

        return SapAccessFormResource::collection($record->latest()->paginate($perPage));
    }
}
