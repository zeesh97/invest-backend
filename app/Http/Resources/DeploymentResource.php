<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class DeploymentResource extends FormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
        [
            // 'id' => $this->id,
            'reference_form_id' => $this->referenceForm ?? null,
            'project_id' => $this->project ?? null,
            'deployment_status' => $this->deployment_status ?? null,
            'reference_details' => $this->referenceDetail ?? null,
            'change_priority' => $this->change_priority ?? null,


            'deploymentDetail' => $this->deploymentDetail && $this->deploymentDetail->isNotEmpty() ?
                DeploymentDetailResource::collection($this->deploymentDetail) : null,
            'attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];
        return array_merge(parent::toArray($request), $specificData);
    }
}
