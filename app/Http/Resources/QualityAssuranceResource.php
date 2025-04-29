<?php

namespace App\Http\Resources;

use App\Enums\FormEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class QualityAssuranceResource extends FormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
        [
            'qa_tester' => $this->qaAssignment->qaUser->only(['id', 'name']) ?? null,
            'status' => $this->qaAssignment->status ?? null,
            'status_at' => $this->qaAssignment->status_at ?? null,
            'feedback' => $this->qaAssignment->feedback ?? null,
            'form_name' => FormEnum::getNameByModel($this->qaAssignment->assurable_type),
            'related_record' => $this->qaAssignment->assurable ?? null,
            'attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];

        // Conditional logic for uatScenarios
        // if ($this->qaAssignment->assurable_type === 'App\Models\Forms\SCRF') {
        //     $specificData['uatScenarios'] = $this->uatScenarios?->isNotEmpty() ?
        //         UatScenarioResource::collection($this->uatScenarios) : null;
        // } elseif ($this->qaAssignment->assurable_type === 'App\Models\Forms\MasterDataManagementForm') {
        //     $specificData['uatScenarios'] = $this->uatScenarios?->isNotEmpty() ?
        //         UatScenarioMDMResource::collection($this->uatScenarios) : null;
        // }

        return array_merge(parent::toArray($request), $specificData);
    }
}
