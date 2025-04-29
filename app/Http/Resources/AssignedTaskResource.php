<?php

namespace App\Http\Resources;

use App\Enums\FormEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedTaskResource extends JsonResource
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
            'form_name' => $this->assignable_type ? FormEnum::getNameByModel($this->assignable_type) : null,
            'assignable_id' => $this->assignable_id,
            'created_at' => $this->created_at ?: null,
            'start_at' => $this->start_at ? $this->start_at : null,
            'due_at' => $this->due_at ? $this->due_at : null,

            'assign_task_teams' => $this->assignTaskTeams->groupBy('team_id')->map(function ($group) {
                return [
                    'team' => $group->first()->team->only(['id', 'name']),
                    'members' => $group->map(function ($item) {
                        return [
                            'id' => $item->member->id,
                            'name' => $item->member->name,
                            'email' => $item->member->email,
                            'start_at' => $item->start_at ? $item->start_at : null,
                            'due_at' => $item->due_at ? $item->due_at : null,
                        ];
                    })->values(),
                ];
            })->values(),

        ];
    }
}
