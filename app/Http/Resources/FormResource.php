<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
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
            'initiator_field_one_id' => $this->initiator_field_one ? $this->initiator_field_one->only(['id', 'name']) : null,
            'initiator_field_two_id' => $this->initiator_field_two ? $this->initiator_field_two->only(['id', 'name']) : null,
            'initiator_field_three_id' => $this->initiator_field_three ? $this->initiator_field_three->only(['id', 'name']) : null,
            'initiator_field_four_id' => $this->initiator_field_four ? $this->initiator_field_four->only(['id', 'name']) : null,
            'initiator_field_five_id' => $this->initiator_field_five ? $this->initiator_field_five->only(['id', 'name']) : null,
            'callback' => $this->callback ?? null
        ];
    }
}
