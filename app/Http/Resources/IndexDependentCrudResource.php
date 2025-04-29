<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexDependentCrudResource extends JsonResource
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
            'type' => $this->type ?? null,
            'parent_id' => $this->parent_id ?? null,
            'company' => $this->company ? [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'logo' => $this->company->logo,
            ] : null,
            'data' => $this->data ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
