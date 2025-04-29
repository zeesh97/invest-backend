<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QaAssignmentResource extends JsonResource
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
            'quality_assurances' =>  $this->whenLoaded('qualityAssurances'),
            'qa_user' => $this->whenLoaded('qaUser'),
            'status' => $this->status,
        ];
    }
}
