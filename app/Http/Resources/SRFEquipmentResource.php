<?php

namespace App\Http\Resources;

use App\Enums\StateEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SRFEquipmentResource extends FormBaseResource
{    private $grandTotal;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $equipmentTotal = $this->equipmentRequests->sum('total');
        $softwareTotal = $this->softwareRequests->sum('total');
        $serviceTotal = $this->serviceRequests->sum('total');
        $this->grandTotal = $equipmentTotal + $softwareTotal + $serviceTotal;
        $specificData =
        [
            // 'id' => $this->id,
            'cost_center_id' => $this->costCenter->only(['id', 'cost_center']),
            'equipmentRequests' => $this->mapEquipmentRequests(),
            'softwareRequests' => $this->mapSoftwareRequests(),
            'serviceRequests' => $this->mapServiceRequests(),
            'grand_total' => $this->grandTotal,
        ];
        return array_merge(parent::toArray($request), $specificData);
    }

    private function mapEquipmentRequests(): array
    {
        return $this->equipmentRequests->map(function ($equipmentRequest) {
            return [
                'id' => $equipmentRequest->id,
                'equipment' => $equipmentRequest->equipment->only(['id', 'name']),
                'qty' => $equipmentRequest->qty,
                'state' => [
                    'id' => $equipmentRequest->state,
                    'name' => StateEnum::getDescription($equipmentRequest->state),
                ],
                'expense_type' => [
                    'id' => $equipmentRequest->expense_type,
                    'name' => $this->mapExpenseType($equipmentRequest->expense_type),
                ],
                'expense_nature' => [
                    'id' => $equipmentRequest->expense_nature,
                    'name' => $this->mapExpenseNature($equipmentRequest->expense_nature),
                ],
                'asset_details' => $equipmentRequest->asset_details,
                'business_justification' => $equipmentRequest->business_justification,
                'amount' => $equipmentRequest->amount,
                'currency' => $equipmentRequest->currency,
                'rate' => $equipmentRequest->rate,
                'total' => $equipmentRequest->total,
            ];
        })->toArray();
    }

    private function mapSoftwareRequests(): array
    {
        return $this->softwareRequests->map(function ($softwareRequest) {
            return [

                'id' => $softwareRequest->id,
                'name' => $softwareRequest->name,
                'version' => $softwareRequest->version,
                'qty' => $softwareRequest->qty,
                'expense_type' => [
                    'id' => $softwareRequest->expense_type,
                    'name' => $this->mapExpenseType($softwareRequest->expense_type),
                ],
                'expense_nature' => [
                    'id' => $softwareRequest->expense_nature,
                    'name' => $this->mapExpenseNature($softwareRequest->expense_nature),
                ],

                'asset_details' => $softwareRequest->asset_details,
                'business_justification' => $softwareRequest->business_justification,
                'amount' => $softwareRequest->amount,
                'currency' => $softwareRequest->currency,
                'rate' => $softwareRequest->rate,
                'total' => $softwareRequest->total,
            ];
        })->toArray();
    }

    private function mapServiceRequests(): array
    {
        return $this->serviceRequests->map(function ($serviceRequest) {
            return [
                'id' => $serviceRequest->id,
                'name' => $serviceRequest->name,
                'state' => [
                    'id' => $serviceRequest->state,
                    'name' => StateEnum::getDescription($serviceRequest->state),
                ],
                'expense_type' => [
                    'id' => $serviceRequest->expense_type,
                    'name' => $this->mapExpenseType($serviceRequest->expense_type),
                ],
                'expense_nature' => [
                    'id' => $serviceRequest->expense_nature,
                    'name' => $this->mapExpenseNature($serviceRequest->expense_nature),
                ],

                'asset_details' => $serviceRequest->asset_details,
                'business_justification' => $serviceRequest->business_justification,
                'amount' => $serviceRequest->amount,
                'currency' => $serviceRequest->currency,
                'rate' => $serviceRequest->rate,
                'total' => $serviceRequest->total,
            ];
        })->toArray();
    }

    // private function mapApprovers(): array
    // {
    //     $approvalStatus = ApprovalStatus::where('form_id', self::FORM_ID)
    //         ->where('key', $this->id)
    //         ->get();

    //     return $approvalStatus
    //         ->groupBy('approver_id')->map(function ($approvers) {
    //             $firstApprover = $approvers->first();
    //             return [
    //                 'id' => $firstApprover->approver->id,
    //                 'name' => $firstApprover->approver->name,
    //                 'users' => $approvers->map(function ($approver) {
    //                     return [
    //                         'id' => $approver->user->id,
    //                         'name' => $approver->user->name,
    //                         'approval_required' => $approver->approval_required,
    //                         'sequence_no' => $approver->sequence_no,
    //                         'status' => $approver->status,
    //                     ];
    //                 }),
    //             ];
    //         })
    //         ->toArray();
    // }

    private function mapExpenseType($expenseType): ?string
    {
        return match ($expenseType) {
            1 => 'Budgeted',
            2 => 'Non-Budgeted',
            default => null,
        };
    }

    private function mapExpenseNature($expenseNature): ?string
    {
        return match ($expenseNature) {
            1 => 'Capital',
            2 => 'Revenue',
            default => null,
        };
    }

    // private function mapCreatedBy(): ?array
    // {
    //     return $this->user ? $this->user->only('id', 'name', 'email', 'employee_no') : null;
    // }

    // private function mapUpdatedBy(): ?array
    // {
    //     return $this->updatedBy ? $this->updatedBy->only('id', 'name', 'email', 'employee_no') : null;
    // }
}


