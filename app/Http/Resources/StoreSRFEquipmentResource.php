<?php

namespace App\Http\Resources;

use App\Enums\StateEnum;
use App\Models\ApprovalStatus;
use Illuminate\Http\Request;

class StoreSRFEquipmentResource extends FormBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $specificData =
        [
            // 'id' => $this->id,
            'cost_center_id' => $this->costCenter->only(['id', 'cost_center']),
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
                    'business_justification' => $equipmentRequest->business_justification,
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
                    'business_justification' => $softwareRequest->business_justification,
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
                    // 'expense_nature' => $serviceRequest->expense_nature,
                    'business_justification' => $serviceRequest->business_justification,
                ];
            }),
        ];
        return array_merge(parent::toArray($request), $specificData);
    }

}
