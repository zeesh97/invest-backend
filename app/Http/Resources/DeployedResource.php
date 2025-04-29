<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeployedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_title' => $this->request_title,
            'reference_form_id' => $this->reference_form_id,
            'reference_details' => $this->reference_details,
            'sequence_no' => $this->sequence_no,
            'created_by' => $this->user,
            'created_at' => $this->created_at,
            'deployment_details' => $this->deploymentDetail->isNotEmpty() ?
                DeploymentDetailResource::collection($this->deploymentDetail) : null,
            'reference_form' => $this->referenceForm ?
                $this->referenceForm : null,
        ];
    }
}
