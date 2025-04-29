<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConditionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $form = $this->form()->with('conditions')->first();

        return [
            'form' => [
                'id' => $form->id,
                'name' => $form->name,

                'conditions' => $form->conditions->map(function ($condition) {
                    return [
                        'id' => $condition->id,
                        'name' => $condition->name,
                    ];
                })->values()->toArray(),
            ],
        ];
    }
}
