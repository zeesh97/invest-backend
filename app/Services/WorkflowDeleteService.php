<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Models\ApprovalStatus;
use App\Models\Workflow;
use App\Models\WorkflowInitiatorField;
use App\Models\WorkflowSubscriberApprover;
use Symfony\Component\HttpFoundation\Response;

class WorkflowDeleteService
{
    public function deleteWorkflow(int $workflowId)
    {
        $workflow = Workflow::find($workflowId);

        if (!$workflow) {
            return Helper::sendError('Workflow not found', [], Response::HTTP_NOT_FOUND);
        }

        if ($this->isWorkflowReferenced($workflow)) {
            return Helper::sendError('Workflow is referenced in other records and cannot be deleted', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->deleteRelatedRecords($workflow);

        $workflowDeleted = $workflow->delete();

        $data['workflow_deleted'] = $workflowDeleted;
        return Helper::sendResponse($data, 'Workflow deleted successfully', 200);
    }

    private function isWorkflowReferenced(Workflow $workflow): bool
    {
        return ApprovalStatus::where('workflow_id', $workflow->id)->exists();
    }

    private function deleteRelatedRecords(Workflow $workflow): void
    {
        WorkflowSubscriberApprover::where('workflow_id', $workflow->id)->delete();
        WorkflowInitiatorField::where('workflow_id', $workflow->id)->delete();
    }
}
