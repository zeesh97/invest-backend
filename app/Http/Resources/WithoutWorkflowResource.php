<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithoutWorkflowResource extends JsonResource
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
            'form' => $this->form?->only(['id', 'name']),
            'software_category' => $this->softwareCategory?->only(['id', 'name']),
            'created_by' => $this->createdBy?->only(['id', 'name']),
            'updated_by' => $this->updatedBy?->only(['id', 'name']),
        ];
    }
}
