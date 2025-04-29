<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class RequestSupportFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sequence_no' => $this->sequence_no,
            'request_title' => $this->request_title,
            'assigned_task' => $this->assignedTask,
            'is_task_assigned' => $this->assignedTask()->exists(),
            'relevant_id' => $this->relevant_id ?? null,
            'priority' => $this->priority ?? null,
            'phone' => $this->phone ?? null,
            'description' => $this->description ?? null,
            'status' => $this->status ?? null,
            'task_status' => $this->taskStatus ?? null,
            'comment_status' => $this->comment_status,
            'draft_at' => $this->draft_at ? $this->draft_at : null,
            'created_by' => $this->user ? $this->user : null,
            'created_at' => $this->created_at ? $this->created_at : null,
            'service' => $this->whenLoaded('service', function () {
                return new ServiceResource($this->service);
            }),
            'department' => $this->whenLoaded('department', function () {
                return new DepartmentResource($this->department);
            }),
            'location' => isset($this->location) ? new LocationResource($this->location) : null,
            'teams' => $this->whenLoaded('teams', function () {
                return $this->teams->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                    ];
                });
            }),
            // 'assigned_task_to_me' => GlobalFormService::validateUserAssignedTask($this->getModelId(), $this->id) ?? null,

            'attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];
    }
}
