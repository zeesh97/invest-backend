<?php

namespace App\Services\Core;

use Auth;
use App\Models\Approver;
use App\Models\Subscriber;
use App\Models\ApprovalStatus;
use App\Jobs\SendApprovalEmailJob;
use App\Jobs\SendApprovalEmailToParallelApproverJob;
use App\Jobs\SendSubscriberEmailJob;

class ProcessApprovalService
{
    public function processApprovals($resultData, $defined, $workflowId, $formId)
    {
        $approvalStatuses = [];
        $firstParallelApprovers = [];
        $allUserIds = [];
        $subscriberIds = [];
        $firstApprover = true;
        $count = 0;
        $workflowSubscribersApprovers = $defined->workflow->workflowSubscribersApprovers->sortBy('sequence_no');

        foreach ($workflowSubscribersApprovers as $approverGroup) {
            $skip = false;
            if (!$this->shouldSkipApproverGroup($resultData, $formId, $approverGroup->condition_id, $approverGroup->sequence_no, $approverGroup->approver_id)) {
                $approver = Approver::with(['users' => function ($query) {
                    $query->select('id', 'name', 'email')
                        ->with('parallelApprovers:id,name,email');
                }])->find($approverGroup->approver_id);

                $userIds = $approver->users()->pluck('users.id')->toArray();
                $allUserIds = array_merge($userIds, $allUserIds);

                foreach ($userIds as $index => $id) {
                    $approvalStatuses[] = $this->createApprovalStatus($workflowId, $approverGroup, $id, $resultData, $formId, $firstApprover, $approver->users[$index]);

                    if ($firstApprover) {
                        $firstParallelApprovers[] = $this->createParallelApproverStatus($workflowId, $approverGroup, $id, $resultData, $formId, $approver->users[$index]);
                    }
                }

                $firstApprover = false;
            } else {
                $count++;
            }
        }

        $allApproversSkipped = count($approvalStatuses) === $count;

        $subscribers = $this->getSubscribers($defined, $resultData, $formId, $subscriberIds);

        if (!empty($approvalStatuses)) {
            $this->saveApprovalStatuses($approvalStatuses, $resultData, $formId);
            $this->dispatchEmails($approvalStatuses, $subscribers, $firstParallelApprovers);
            return $this->responseData($resultData, $allUserIds, $subscriberIds, $firstParallelApprovers);
        }

        if ($allApproversSkipped) {
            $resultData->update(['status' => 'Approved']);
        }

        return $this->responseData($resultData, $allUserIds, $subscriberIds, []);
    }

    protected function shouldSkipApproverGroup($resultData, $formId, $conditionId, $sequence_no, $approver_id): bool
    {
        if (!is_null($conditionId)) {
            $check = new ApprovalConditionService;
            return $check->approvalConditions($resultData, $formId, $conditionId, $sequence_no, $approver_id);
            // return !$this->checkApprovalConditions($resultData, $approverGroup->approval_condition);
        }
        return false;
    }

    protected function createApprovalStatus($workflowId, $approverGroup, $userId, $resultData, $formId, $firstApprover, $user)
    {
        // dd($user);
        return [
            'workflow_id' => $workflowId,
            'approver_id' => $approverGroup->approver_id,
            'user_id' => $userId,
            'condition_id' => $approverGroup->approval_condition,
            'approval_required' => $user->pivot->approval_required,
            'sequence_no' => $user->pivot->sequence_no,
            'key' => $resultData->id,
            'form_id' => $formId,
            'status' => $firstApprover ? 'Processing' : 'Pending',
            'reason' => null,
            'status_at' => null,
            'responded_by' => null,
            'editable' => $approverGroup->editable ? 1 : 0,
        ];
    }

    protected function createParallelApproverStatus($workflowId, $approverGroup, $userId, $resultData, $formId, $user)
    {
        return [
            'workflow_id' => $workflowId,
            'approver_id' => $approverGroup->approver_id,
            'user_id' => $userId,
            'approval_required' => $user->pivot->approval_required,
            'sequence_no' => $user->pivot->sequence_no,
            'key' => $resultData->id,
            'form_id' => $formId,
            'status' => 'Processing',
            'parallel_user_id' => $user->parallelApprovers ?? null,
        ];
    }

    protected function getSubscribers($defined, $resultData, $formId, &$subscriberIds)
    {
        $subscribers = [];

        foreach ($defined->workflow->workflowSubscribersApprovers as $subscriberGroup) {
            $subscriber = Subscriber::with('users:id,name,email,employee_no')->find($subscriberGroup->subscriber_id);
            if (!is_null($subscriber)) {
                $subscriberIds = array_merge($subscriberIds, $subscriber->users()->pluck('users.id')->toArray());
                foreach ($subscriber->users as $index => $user) {
                    $subscribers[] = [
                        'subscriber_id' => $subscriberGroup->subscriber_id,
                        'user_id' => $user->id,
                        'key' => $resultData->id,
                        'form_id' => $formId,
                        'email' => $user->email,
                        'name' => $user->name,
                        'employee_no' => $user->employee_no,
                    ];
                }
            }
        }

        return $subscribers;
    }

    protected function saveApprovalStatuses($approvalStatuses, $resultData, $formId)
    {
        try {
            ApprovalStatus::insert($approvalStatuses);

            $allApproved = !ApprovalStatus::where('form_id', $formId)
                ->where('key', $resultData->id)
                ->where('status', '<>', 'Approved')
                ->exists();

            if ($allApproved) {
                $resultData->update(['status' => 'Approved']);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function dispatchEmails($approvalStatuses, $subscribers, $parallelApprovers)
    {
        dispatch(new SendApprovalEmailJob($approvalStatuses));

        if (!empty($subscribers)) {
            dispatch(new SendSubscriberEmailJob($subscribers));
        }

        if (!empty($parallelApprovers)) {
            dispatch(new SendApprovalEmailToParallelApproverJob($parallelApprovers));
        }
    }

    protected function responseData($resultData, $allUserIds, $subscriberIds, $parallelApprovers)
    {
        return [
            'resultData' => $resultData,
            'approverIds' => $allUserIds,
            'subscriberIds' => $subscriberIds,
            'parallelApproverIds' => array_column($parallelApprovers, 'parallel_user_id'),
            'created_by' => Auth::user()->id,
        ];
    }

    protected function checkApprovalConditions($data, $conditionId)
    {
        switch ($conditionId) {
            case 1:
                return $data->change_significance == 'Major';
            case 2:
                return $data->change_significance == 'Minor';
            case 3:
                return $data->location_id == 2;
            case 4:
                return $data->created_at >= '2024-01-01' && $data->created_at <= '2024-12-31';
            case 5:
                $totalSum = $this->getTotalRequestSum($data);
                return $totalSum >= 1000000 && $totalSum <= 2000000;
            case 6:
                $totalSum = $this->getTotalRequestSum($data);
                return $totalSum >= 1 && $totalSum < 1000000;
            case 7:
                return !$this->hasNonExpenseNature($data, 1);
            case 8:
                return !$this->hasNonExpenseNature($data, 2);
            default:
                return false;
        }
    }

    protected function getTotalRequestSum($data)
    {
        return $data->equipmentRequests->sum('total') + $data->softwareRequests->sum('total') + $data->serviceRequests->sum('total');
    }

    protected function hasNonExpenseNature($data, $nature)
    {
        return $data->equipmentRequests()->where('expense_nature', '!=', $nature)->exists() ||
            $data->softwareRequests()->where('expense_nature', '!=', $nature)->exists() ||
            $data->serviceRequests()->where('expense_nature', '!=', $nature)->exists();
    }
}
