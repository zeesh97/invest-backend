<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutoAssignTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'form' => $this->form?->only(['id', 'name']),
            'user' => $this->user?->only(['id', 'name']),
            'teams' => $this->teamsWithMembers(),
        ];
    }
}
