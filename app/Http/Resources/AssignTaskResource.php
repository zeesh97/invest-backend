<?php

namespace App\Http\Resources;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\SlugGenerator;

class AssignTaskResource extends JsonResource
{
    use SlugGenerator;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assignedTaskTeams = $this->assignTaskTeams->groupBy('team_id')->map(function ($team) {
            return [
                'team' => $team->first()->team->only(['id', 'name']),
                'members' => $team->map(fn($assignTaskTeam) => $assignTaskTeam->member->only(['id', 'name', 'email'])),
            ];
        });

        $formDetails = $this->assignable_type ? $this->findFormDetailsByClass($this->assignable_type) : null;

        return [
            'id' => $this->id,
            'start_at' => $this->start_at ? $this->start_at : null,
            'due_at' => $this->due_at ? $this->due_at : null,
            'task_assigned_by' => $this->taskAssignedBy?->only(['id', 'name']),
            'form' => $formDetails,
            'key_entry' => $this->assignable_id,
            'url' => $formDetails ? config('app.frontend_url') . '/' . $formDetails['slug'] . '/details/' . $this->assignable_id : null,
            'task_name' => $this->assignable?->request_title,
            'sequence_no' => $this->assignable?->sequence_no,
            'task_status' => $this->assignable?->taskStatus,
            'assigned_task_team' => $assignedTaskTeams->values(),
        ];
    }

    /**
     * Find form details by class name.
     *
     * @param string $className
     * @return array|null
     */
    protected function findFormDetailsByClass(string $className): ?array
    {
        return Form::where('identity', $className)
                   ->select(['id', 'name', 'slug'])
                   ->first()
                   ?->only(['id', 'name', 'slug']);
    }
}
