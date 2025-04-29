<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestSupportDeskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assignedTaskTeams = ($this->assignedTask && !is_null($this->assignedTask->assignTaskTeams)) ?
        $this->assignedTask->assignTaskTeams->groupBy('team_id')->map(function ($team) {
            return [
                'team' => $team->first()->team->only(['id', 'name']),
                'managers' => $team->first()->team->managers->map(function ($manager) {
                    return $manager->only(['id', 'name', 'email']);
                }),
                'members' => $team->map(function ($assignTaskTeam) {
                    return $assignTaskTeam->member->only(['id', 'name', 'email']);
                }),
            ];
        })->values() : null;

    $taskInitiatedAt = $this->created_at ? $this->created_at : null;
    $taskAssignedAt = $this->assignedTask ? $this->assignedTask->created_at : null;

    return [
        'id' => $this->id,
        'sequence_no' => $this->sequence_no,
        'request_title' => $this->request_title,
        'location' => ($this->location) ? $this->location->only(["id", "name"]) : null,
        'department' => ($this->department) ? $this->department->only(["id", "name"]) : null,
        'created_by' => ($this->user) ? $this->user->only(["id", "name"]) : null,
        'updated_by' => ($this->updatedBy) ? $this->updatedBy->only(["id", "name"]) : null,
        'task_approval_at' => $this->task_status_at ? $this->task_status_at : null,
        'task_initiated_at' => $taskInitiatedAt,
        'task_assigned_at' => $taskAssignedAt,
        'task_status' => $this->taskStatusName ?? null,
        'task_assigned' => (isset($this->assignedTask)) ? $this->assignedTask->only(['id']) : null,
        'task_assigned_teams' => $assignedTaskTeams,
        // 'is_task_assigned' => $this->assignedTask()->exists(),
        'comment_status' => $this->comment_status,
        'status' => $this->status,
        'deleted_at' => $this->deleted_at ? $this->deleted_at : null,
        'created_at' => $this->created_at ? $this->created_at : null,
        'updated_at' => $this->updated_at ? $this->updated_at : null,
    ];

    }
}
