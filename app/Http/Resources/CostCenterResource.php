<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostCenterResource extends JsonResource
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
            'cost_center' => $this->cost_center,
            'department' => $this->department->only(['id','name']),
            'location' => $this->location->only(['id','name']),
            'description' => $this->description,
            'project' => $this->project,
        ];
    }
}
