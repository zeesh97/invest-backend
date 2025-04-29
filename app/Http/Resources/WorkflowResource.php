<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $form = optional($this->workflowInitiatorField)->form;
        $workflowInitiator = optional($this->workflowInitiatorField)->workflowInitiator;

        return [
            'id' => $this->id,
            'name' => $this->name ?: null,
            'created_by_id' => optional($this->created_by)->only('id', 'name'),
            'callback' => $this->callback ?: null,
            'form' => $form ? $form->only('id', 'name') : null,
            'workflow_initiator' => $workflowInitiator ? $workflowInitiator->only('id', 'name') : null,
            'key_one' => !is_null($this->workflowInitiatorField?->initiator_field_one) ? $this->mapIdentity($this->workflowInitiatorField->initiator_field_one, $this->workflowInitiatorField->key_one) : null,
            'key_two' => !is_null($this->workflowInitiatorField?->initiator_field_two) ? $this->mapIdentity($this->workflowInitiatorField->initiator_field_two, $this->workflowInitiatorField->key_two) : null,
            'key_three' => !is_null($this->workflowInitiatorField?->initiator_field_three) ? $this->mapIdentity($this->workflowInitiatorField->initiator_field_three, $this->workflowInitiatorField->key_three) : null,
            'key_four' => !is_null($this->workflowInitiatorField?->initiator_field_four) ? $this->mapIdentity($this->workflowInitiatorField->initiator_field_four, $this->workflowInitiatorField->key_four) : null,
            'key_five' => !is_null($this->workflowInitiatorField?->initiator_field_five) ? $this->mapIdentity($this->workflowInitiatorField->initiator_field_five, $this->workflowInitiatorField->key_five) : null,
            // 'key_two' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_two, 'two'),
            // 'key_three' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_three, 'three'),
            // 'key_four' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_four, 'four'),
            // 'key_five' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_five, 'five'),
            'approvers_subscribers' => $this->mapApproversSubscribers(),
            // 'approval_statuses' => ApprovalStatusResource::collection($this->approvalStatuses)
        ];
    }

    protected function mapIdentity($initiatorField, $key): ?array
    {
        $identity = $initiatorField->identity ?? null;

        if (is_null($identity)) {
            return null;
        }

        $result = $identity::find($key);

        return $result ? [
            'id' => $result->id,
            'name' => $result->name,
        ] : null;
    }

    protected function mapApproversSubscribers()
    {
        return $this->workflowSubscribersApprovers->map(function ($approverSubscriber): array {
            return [
                'approval_condition' => $approverSubscriber->condition,
                'sequence_no' => $approverSubscriber->sequence_no,
                'editable' => $approverSubscriber->editable,
                'approver' => optional($approverSubscriber->approver)->only(['id', 'name']),
                'subscriber' => optional($approverSubscriber->subscriber)->only(['id', 'name']),
            ];
        })->toArray();
    }
}



/*
    public function toArray(Request $request): array
    {
        $form = optional($this->workflowInitiatorField)->form;
        $workflowInitiator = optional($this->workflowInitiatorField)->workflowInitiator;

        return [
            'id' => $this->id,
            'name' => $this->name ?: null,
            'created_by_id' => optional($this->created_by)->only('id', 'name'),
            'callback' => $this->callback ?: null,
            'form' => $form ? $form->only('id', 'name') : null,
            'workflow_initiator' => $workflowInitiator ? $workflowInitiator->only('id', 'name') : null,
            'key_one' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_one, 'one'),
            'key_two' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_two, 'two'),
            'key_three' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_three, 'three'),
            'key_four' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_four, 'four'),
            'key_five' => $this->mapIdentity($form, optional($this->workflowInitiatorField)->key_five, 'five'),
            'approvers_subscribers' => $this->mapApproversSubscribers(),
            // 'approval_statuses' => ApprovalStatusResource::collection($this->approvalStatuses)
        ];
    }

    protected function mapIdentity($form, $key, $slug_key): ?array
    {
        if (is_null($form) || is_null($this->id) || is_null($key)) {
            return null;
        }

        $slug = "initiator_field_$slug_key";
        $methodName = $slug;
        $identity = optional($form->$methodName)->identity;
        $result = $identity ? $identity::find($key) : null;

        return $result ? [
            'id' => $result->id,
            'name' => $result->name,
        ] : null;
    }

    protected function mapApproversSubscribers()
    {
        return $this->workflowSubscribersApprovers->map(function ($approverSubscriber): array {
            return [
                'approval_condition' => $approverSubscriber->condition,
                'sequence_no' => $approverSubscriber->sequence_no,
                'editable' => $approverSubscriber->editable,
                'approver' => optional($approverSubscriber->approver)->only(['id', 'name']),
                'subscriber' => optional($approverSubscriber->subscriber)->only(['id', 'name']),
            ];
        })->toArray();
    }
}

*/
