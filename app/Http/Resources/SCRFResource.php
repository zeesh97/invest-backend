<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SCRFResource extends FormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
        [
            // 'id' => $this->id,
            'request_specs' => $this->request_specs ?? null,
            'change_type' => $this->change_type ?? null,
            'change_priority' => $this->change_priority ?? null,
            'process_efficiency' => $this->process_efficiency ?? null,
            'cost_saved' => $this->cost_saved ?? null,
            'controls_improved' => $this->controls_improved ?? null,
            'legal_reasons' => $this->legal_reasons ?? null,
            'man_hours' => $this->man_hours ?? null,
            'other_benefits' => $this->other_benefits ?? null,
            'change_significance' => $this->change_significance ?? null,
            'software_category' => $this->software_category ? new SoftwareCategoryResource($this->software_category): null,
            'software_subcategories' => $this->software_subcategories->isNotEmpty() ?
            SoftwareSubcategoryResource::collection($this->software_subcategories) : null,

            'uatScenarios' => $this->uatScenarios && $this->uatScenarios->isNotEmpty() ?
                UatScenarioResource::collection($this->uatScenarios) : null,
            'attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];
        return array_merge(parent::toArray($request), $specificData);
    }
}
