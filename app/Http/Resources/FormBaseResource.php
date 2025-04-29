<?php

namespace App\Http\Resources;


use App\Services\GlobalFormService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormBaseResource extends JsonResource
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
            'sequence_no' => $this->sequence_no,
            'request_title' => $this->request_title,
            'assigned_task' => $this->assignedTask ? new AssignedTaskResource($this->assignedTask) : null,
            'is_task_assigned' => $this->assignedTask()->exists(),
            'approvers' => self::mapApprovers(),
            'approved_disapproved' => $this->mapApproveDisapprove(),
            'parallel_approved_disapproved' => $this->mapParallelApproveDisapprove(),
            'parallel_approved_disapproved_users' => $this->mapParallelUsers(),
            'deployments' => $this->deployments->isNotEmpty() ?
                DeployedResource::collection($this->deployments) : null,
            'status' => $this->status ?? null,
            'task_status' => $this->taskStatus ?? null,
            'comment_status' => $this->comment_status,
            'draft_at' => $this->draft_at ?? null,
            'created_by' => $this->user ? $this->user->only('id', 'name', 'email', 'employee_no') : null,
            'created_at' => $this->created_at ? $this->created_at : null,
            'department' => $this->whenLoaded('department', function () {
                return new DepartmentResource($this->department);
            }),
            'location' => isset($this->location) ? new LocationResource($this->location) : null,
            'designation' => isset($this->designation) ? new DesignationResource($this->designation) : null,
            'section' => isset($this->section) ? new SectionResource($this->section) : null,
            // 'business_expert' => isset($this->user->business_expert) ? new BusinessExpertResource($this->user->business_expert) : null,
            'assigned_task_to_me' => GlobalFormService::validateUserAssignedTask($this->getModelId(), $this->id) ?? null
        ];
    }

    private function mapApprovers(): array
    {
        $timezone = \App\Http\Helpers\Helper::appTimezone();
        return $this->approvalStatuses
            ->groupBy('approver_id')
            ->map(function ($group) use ($timezone) {
                $firstApproval = $group->first();
                return [
                    'id' => $firstApproval->approver->id,
                    'name' => $firstApproval->approver->name,
                    'condition' => $firstApproval->condition_id,
                    'users' => $group->map(function ($approval) use ($timezone) {
                        return [
                            'id' => $approval->user->id,
                            'name' => $approval->user->name,
                            'approval_required' => $approval->approval_required,
                            'user_sequence_no' => $approval->user_sequence_no,
                            'sequence_no' => $approval->sequence_no,
                            'status' => $approval->status,
                            'status_at' => !is_null($approval->status_at) ? $approval->status_at : null,
                            'responded_by' => $approval->respondedBy ? $approval->respondedBy->only('id', 'name') : null,
                            'is_parallel' => (bool) $approval->is_parallel,
                            'editable' => $approval->editable,
                        ];
                    }),
                ];
            })
            ->values()
            ->toArray();
    }

    private function mapApproveDisapprove(): bool
    {
        return $this->approvalStatuses
            ->contains(function ($approvalStatus) {
                return $approvalStatus->status === 'Processing' &&
                    $approvalStatus->user_id === Auth::user()->id;
            });
    }
    private function mapParallelApproveDisapprove()
    {
        return $this->approvalStatuses->some(function ($approvalStatus) {
            return $approvalStatus->status === 'Processing' &&
                $approvalStatus->user->parallelApprovers->contains(function ($parallelApprover) {
                    return $parallelApprover->pivot->parallel_user_id === Auth::user()->id;
                });
        });
    }

    private function mapParallelUsers()
    {
        return $this->approvalStatuses
            ->filter(function ($approvalStatus) {
                return $approvalStatus->status === 'Processing' && $approvalStatus->user->parallelApprovers->contains(function ($parallelApprover) {
                    return $parallelApprover->id === Auth::user()->id;
                });
            })
            ->map(function ($approvalStatus) {
                return [
                    'id' => $approvalStatus->user->id,
                    'name' => $approvalStatus->user->name,
                    'employee_no' => $approvalStatus->user->employee_no,
                    'email' => $approvalStatus->user->email,
                ];
            })->values()
            ->toArray();
    }
    // private function mapCreatedBy(): ?array
    // {
    //     return $this->user ? $this->user->only('id', 'name', 'email', 'employee_no') : null;
    // }

    // private function mapUpdatedBy(): ?array
    // {
    //     return $this->updatedBy ? $this->updatedBy->only('id', 'name', 'email', 'employee_no') : null;
    // }
}
