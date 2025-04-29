<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MasterDataManagementFormResource extends FormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
        [
            // 'id' => $this->id,
            'request_specs' => $this->request_specs ?? null,
            'change_priority' => $this->change_priority ?? null,
            'software_category' => $this->software_category ? new SoftwareCategoryResource($this->software_category): null,
            'mdm_category' => $this->mdm_category ? new MdmCategoryResource($this->mdm_category): null,
            'project_MDM' => $this->projectMDM ?? null,
            'software_subcategories' => $this->software_subcategories->isNotEmpty() ?
            SoftwareSubcategoryResource::collection($this->software_subcategories) : null,

            'uatScenarios' => $this->uatScenarios && $this->uatScenarios->isNotEmpty() ?
                UatScenarioMDMResource::collection($this->uatScenarios) : null,
            'attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];
        return array_merge(parent::toArray($request), $specificData);
    }
}
