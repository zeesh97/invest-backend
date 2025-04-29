<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Http\Resources\WorkflowResource;
use App\Models\Workflow;
use Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class WorkflowService
{
    public function index()
    {
        try {
            $response = [];
            $perPage = 10; // Adjust the number of items per page as needed
            $workflows = $this->getWorkflows();

            if ($workflows->isEmpty()) {
                return Helper::sendError("No workflows found.", [], 404);
            }

            $responseData = $this->formatWorkflows($workflows);

            $page = request('page', 1);

            $responseData = $responseData->forPage($page, $perPage);

            $paginator = new LengthAwarePaginator(
                $responseData,
                $responseData->count(),
                $perPage,
                $page
            );

            // return Helper::sendResponse($paginator, 'Success', 200);
            return response()->json($paginator, $status = 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    private function getWorkflows()
    {
        $query = Workflow::with([
            'workflowSubscribersApprovers',
            'workflowSubscribersApprovers.condition:id,name,form_id',
            'approvalStatuses',
            'workflowInitiatorField',
            'created_by',
            'approvers',
            'subscribers'
        ]);

        if (!Auth::user()->hasRole('admin')) {
            $query->where('created_by_id', Auth::user()->id);
        }

        return WorkflowResource::collection($query->latest()->get());
    }

    private function formatWorkflows($workflows)
    {
        return $workflows->map(function ($workflow, $index) {
            return [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'callback' => $workflow->callback,
                'created_by' => [
                    'id' => $workflow->created_by->id,
                    'name' => $workflow->created_by->name
                ],
                'workflow_initiator_field' => [
                    'key_one' => $workflow->workflowInitiatorField->keyOne,
                    'key_two' => $workflow->workflowInitiatorField->keyTwo,
                    'key_three' => $workflow->workflowInitiatorField->keyThree,
                    'key_four' => $workflow->workflowInitiatorField->keyFour,
                    'key_five' => $workflow->workflowInitiatorField->keyFive,
                ],
                // 'workflow_initiator_field' => [
                //     'key_one' => $workflow->workflowInitiatorField->form->initiator_field_one->identity::find($workflow->workflowInitiatorField->key_one),
                //     'key_two' => $workflow->workflowInitiatorField->form->initiator_field_two->identity::find($workflow->workflowInitiatorField->key_two),
                //     'key_three' => $workflow->workflowInitiatorField->form->initiator_field_three->identity::find($workflow->workflowInitiatorField->key_three),
                //     'key_four' => $workflow->workflowInitiatorField->form->initiator_field_four->identity::find($workflow->workflowInitiatorField->key_four),
                //     'key_five' => $workflow->workflowInitiatorField->form->initiator_field_five->identity::find($workflow->workflowInitiatorField->key_five),
                // ],
                'form' => $workflow->workflowInitiatorField->form->only('id', 'name') ?: null,
                'workflow_initiator' => $workflow->workflowInitiatorField->workflowInitiator->only('id', 'name') ?: null,
                'workflow_subscribers_approvers' => $workflow->workflowSubscribersApprovers->map(function ($wsa, $index) {
                    return [
                        'id' => $wsa->id,
                        'approval_condition' => $wsa->condition,
                        'sequence_no' => $wsa->sequence_no,
                        'approver' => [
                            'id' => $wsa->approver->id,
                            'name' => $wsa->approver->name,
                        ],
                        'subscriber' => [
                            'id' => $wsa->subscriber->id,
                            'name' => $wsa->subscriber->name,
                        ],
                    ];
                })
            ];
        });
    }
}
