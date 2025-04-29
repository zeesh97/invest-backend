<?php

namespace App\Http\Resources;

use App\Enums\StateEnum;
use App\Models\ApprovalStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateCRFResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    private const FORM_ID = 4;

    private $grandTotal;
    public function toArray(Request $request): array
    {
        $equipmentTotal = $this->equipmentRequests->sum('total');
        $softwareTotal = $this->softwareRequests->sum('total');
        $serviceTotal = $this->serviceRequests->sum('total');
        $this->grandTotal = $equipmentTotal + $softwareTotal + $serviceTotal;

        return [
            'id' => $this->id,
            'sequence_no' => $this->sequence_no,
            'request_title' => $this->request_title,
            'cost_center_id' => $this->costCenter->only(['id', 'cost_center']),
            'for_employee' => $this->for_employee,
            'for_department' => new DepartmentResource($this->forDepartment),
            'equipmentRequests' => $this->equipmentRequests->map(function ($equipmentRequest) {
                return [
                    'id' => $equipmentRequest->id,
                    'equipment' => $equipmentRequest->equipment->only(['id', 'name']),
                    'qty' => $equipmentRequest->qty,
                    'state' => [
                        'id' => $equipmentRequest->state,
                        'name' => match ($equipmentRequest->state) {
                            1 => 'New',
                            2 => 'Renew',
                            3 => 'Repair',
                            4 => 'Replace',
                            5 => 'Temporary',
                            6 => 'Upgrade',
                            default => null,
                        }
                    ],
                    // 'state' =>  $equipmentRequest->state,
                    'expense_type' => [
                        'id' => $equipmentRequest->expense_type,
                        'name' => match ($equipmentRequest->expense_type) {
                            1 => 'Budgeted',
                            2 => 'Non-Budgeted',
                            default => null,
                        }
                    ],
                    'expense_nature' => [
                        'id' => $equipmentRequest->expense_nature,
                        'name' => match ($equipmentRequest->expense_nature) {
                            1 => 'Capital',
                            2 => 'Revenue',
                            default => null,
                        }
                    ],
                    // 'expense_nature' => $equipmentRequest->expense_nature,
                    // 'assets' => $this->assetDetails->where('request_type', 'equipment_request')->map(function ($assetDetail) {
                    //     return [
                    //         'action' => $assetDetail->action,
                    //         'request_type' => $assetDetail->request_type,
                    //         'inventory_status' => $assetDetail->inventory_status,
                    //         'expected_expense' => $assetDetail->expected_expense,
                    //         'serial_no' => $assetDetail->serial_no,
                    //         'asset_code' => $assetDetail->asset_code,
                    //         'description' => $assetDetail->description,
                    //         'remarks' => $assetDetail->remarks,
                    //     ];
                    // }),
                    'asset_details' => $equipmentRequest->asset_details,
                    'business_justification' => $equipmentRequest->business_justification,
                    'amount' => $equipmentRequest->amount,
                    'currency' => $equipmentRequest->currency,
                    'rate' => $equipmentRequest->rate,
                    'total' => $equipmentRequest->total,
                ];
            }),
            'softwareRequests' => $this->softwareRequests->map(function ($softwareRequest) {
                return [
                    'id' => $softwareRequest->id,
                    'name' => $softwareRequest->name,
                    'version' => $softwareRequest->version,
                    'qty' => $softwareRequest->qty,
                    'expense_type' => [
                        'id' => $softwareRequest->expense_type,
                        'name' => match ($softwareRequest->expense_type) {
                            1 => 'Budgeted',
                            2 => 'Non-Budgeted',
                            default => null,
                        }
                    ],
                    // 'expense_type' => $softwareRequest->expense_type,
                    'expense_nature' => [
                        'id' => $softwareRequest->expense_nature,
                        'name' => match ($softwareRequest->expense_nature) {
                            1 => 'Capital',
                            2 => 'Revenue',
                            default => null,
                        }
                    ],
                    // 'expense_nature' => $softwareRequest->expense_nature,
                    // 'assets' => $this->assetDetails->where('request_type', 'software_request')->map(function ($assetDetail) {
                    //     return [
                    //         'action' => $assetDetail->action,
                    //         'request_type' => $assetDetail->request_type,
                    //         'inventory_status' => $assetDetail->inventory_status,
                    //         'expected_expense' => $assetDetail->expected_expense,
                    //         'serial_no' => $assetDetail->serial_no,
                    //         'asset_code' => $assetDetail->asset_code,
                    //         'description' => $assetDetail->description,
                    //         'remarks' => $assetDetail->remarks,
                    //     ];
                    // }),

                    'asset_details' => $softwareRequest->asset_details,
                    'business_justification' => $softwareRequest->business_justification,
                    'amount' => $softwareRequest->amount,
                    'currency' => $softwareRequest->currency,
                    'rate' => $softwareRequest->rate,
                    'total' => $softwareRequest->total,
                ];
            }),
            'serviceRequests' => $this->serviceRequests->map(function ($serviceRequest) {
                return [
                    'id' => $serviceRequest->id,
                    'name' => $serviceRequest->name,
                    'state' => [
                        'id' => $serviceRequest->state,
                        'name' => match ($serviceRequest->state) {
                            1 => 'New',
                            2 => 'Renew',
                            3 => 'Repair',
                            4 => 'Replace',
                            5 => 'Temporary',
                            6 => 'Upgrade',
                            default => null,
                        }
                    ],
                    // 'state' => $serviceRequest->state,
                    'expense_type' => [
                        'id' => $serviceRequest->expense_type,
                        'name' => match ($serviceRequest->expense_type) {
                            1 => 'Budgeted',
                            2 => 'Non-Budgeted',
                            default => null,
                        }
                    ],
                    // 'expense_type' => $serviceRequest->expense_type,
                    'expense_nature' => [
                        'id' => $serviceRequest->expense_nature,
                        'name' => match ($serviceRequest->expense_nature) {
                            1 => 'Capital',
                            2 => 'Revenue',
                            default => null,
                        }
                    ],
                    // 'assets' => $this->assetDetails->where('request_type', 'service_request')->map(function ($assetDetail) {
                    //     return [
                    //         'action' => $assetDetail->action,
                    //         'request_type' => $assetDetail->request_type,
                    //         'inventory_status' => $assetDetail->inventory_status,
                    //         'expected_expense' => $assetDetail->expected_expense,
                    //         'serial_no' => $assetDetail->serial_no,
                    //         'asset_code' => $assetDetail->asset_code,
                    //         'description' => $assetDetail->description,
                    //         'remarks' => $assetDetail->remarks,
                    //     ];
                    // }),

                    'asset_details' => $serviceRequest->asset_details,
                    // 'expense_nature' => $serviceRequest->expense_nature,
                    'business_justification' => $serviceRequest->business_justification,
                    'amount' => $serviceRequest->amount,
                    'currency' => $serviceRequest->currency,
                    'rate' => $serviceRequest->rate,
                    'total' => $serviceRequest->total,
                ];
            }),
            'grand_total' => $this->grandTotal,
            'approvers' => $this->mapApprovers(),
            'department' => new DepartmentResource($this->user->department),
            'location' => new LocationResource($this->location),
            'designation' => new DesignationResource($this->user->designation),
            'section' => new SectionResource($this->user->section),
            'approved_disapproved' => $this->approved_disapproved,
            'status' => $this->status ?? null,
            'draft_at' => $this->draft_at ?? null,
            'created_by' => $this->mapCreatedBy(),
            'updated_by' => $this->mapUpdatedBy(),
            'created_at' => $this->created_at->format('d-m-Y'),
        ];
    }

    private function mapApprovers(): array
    {
        $approvalStatus = ApprovalStatus::where('form_id', self::FORM_ID)
            ->where('key', $this->id)
            ->get();

        return $approvalStatus
            ->groupBy('approver_id')->map(function ($approvers) {
                $firstApprover = $approvers->first();
                return [
                    'id' => $firstApprover->approver->id,
                    'name' => $firstApprover->approver->name,
                    'users' => $approvers->map(function ($approver) {
                        return [
                            'id' => $approver->user->id,
                            'name' => $approver->user->name,
                            'approval_required' => $approver->approval_required,
                            'sequence_no' => $approver->sequence_no,
                            'status' => $approver->status,
                        ];
                    }),
                ];
            })
            ->toArray();
    }

    private function mapCreatedBy(): ?array
    {
        return $this->user ? $this->user->only('id', 'name', 'email', 'employee_no') : null;
    }

    private function mapUpdatedBy(): ?array
    {
        return $this->updatedBy ? $this->updatedBy->only('id', 'name', 'email', 'employee_no') : null;
    }
}
