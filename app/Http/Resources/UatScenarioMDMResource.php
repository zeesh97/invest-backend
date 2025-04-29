<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UatScenarioMDMResource extends JsonResource
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
            'master_data_management_form_id' => $this->master_data_management_form_id,
            'detail' => $this->detail,
            'status' => $this->status
        ];
    }
}
