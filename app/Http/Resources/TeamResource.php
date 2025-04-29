<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'name' => $this->name,
            'forms' => $this->forms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'name' => $form->name,
                ];
            }),
            'locations' => LocationResource::collection($this->locations),
            'managers' => $this->managers->map(function ($manager) {
                return [
                    'id' => $manager->id,
                    'name' => $manager->name,
                ];
            }),
        ];
    }
}
