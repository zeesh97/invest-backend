<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoftwareSubcategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ? $this->id: null,
            'name' => $this->name ? $this->name: null,
            'software_category' => new SoftwareCategoryResource($this->whenLoaded('software_category')),
        ];
    }
}
