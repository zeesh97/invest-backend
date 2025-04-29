<?php

namespace App\Services;

use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use App\Jobs\SendApprovalEmailJob;
use App\Jobs\SendApprovalEmailToParallelApproverJob;
use App\Jobs\SendSubscriberEmailJob;
use App\Jobs\UpdateApprovalStatusJob;
use App\Models\ApprovalStatus;
use App\Models\Approver;
use App\Models\AssignTask;
use App\Models\Department;
use App\Models\Form;
use App\Models\FormPermission;
use App\Models\FormPermissionable;
use App\Models\FormRole;
use App\Models\Location;
use App\Models\OtherDependent;
use App\Models\QaAssignment;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\SoftwareCategory;
use App\Models\Subscriber;
use App\Models\UserAccessLevel;
use App\Services\ConditionActions\ConditionCRF;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GlobalFormService
{
    public static function processApprovals($resultData, $defined, $workflowId, $formId)
    {
        $approvalStatuses = [];
        $parallelApprovers = [];
        $firstParallelApprovers = [];
        $userIds = [];
        $allUserIds = [];
        $subscriberIds = [];
        $firstApprover = true;
        $matched = false;
        $allApproversSkipped = true;
        $firstApproverId = null;
        $count = 0;

        $workflowSubscribersApprovers = is_array($defined) ? $defined : $defined->toArray();

        foreach ($workflowSubscribersApprovers as $approverGroup) {

            $skip = false;

            if (!is_null($approverGroup['approval_condition'])) {

                $matched = self::approvalConditions($resultData, $formId, $approverGroup['approval_condition'], $approverGroup['sequence_no'], $approverGroup['approver_id']);

                if ($matched === false) {
                    $skip = true;
                    $count++;
                }
            }

            /* change start */
            $approver = Approver::with(['users' => function ($query) {
                $query->select('id', 'name', 'email')
                    ->with('parallelApprovers:id,name,email');
            }])->find($approverGroup['approver_id']);

            $approver_id = $approverGroup['approver_id'];
            $userIds = $approver->users()->pluck('users.id')->toArray();
            $allUserIds = array_merge($userIds, $allUserIds);
            /* change end */

            if ($skip === false || is_null($approverGroup['approval_condition'])) {

                foreach ($userIds as $index => $id) {

                    if (!is_null($firstApproverId) && $firstApproverId !== $approver_id) {
                        $firstApprover = false;
                    }

                    $approvalStatuses[] = [
                        'workflow_id' => $workflowId,
                        'approver_id' => $approver_id,
                        'subscriber_id' => $approverGroup['subscriber_id'],
                        'user_id' => $id,
                        'condition_id' => $approverGroup['approval_condition'],
                        'approval_required' => $approver->users[$index]->pivot->approval_required,
                        'sequence_no' => $approverGroup['sequence_no'],
                        'user_sequence_no' => $approver->users[$index]->pivot->sequence_no,
                        'key' => $resultData->id,
                        'form_id' => $formId,
                        'status' => $firstApprover ? 'Processing' : 'Pending',
                        'reason' => null,
                        'status_at' => null,
                        'responded_by' => null,
                        'editable' => ($approverGroup['editable'] == 1) ? 1 : 0,
                    ];

                    if ($firstApprover === true) {
                        $firstApproverId = $approver_id;
                        // if (is_array($approver->users[$index]->parallelApprovers) && !empty($approver->users[$index]->parallelApprovers)) {
                        //     $firstParallelApprovers[] = [
                        //         'workflow_id' => $workflowId,
                        //         'approver_id' => $firstApproverId,
                        //         'user_id' => $id,
                        //         'approval_required' => $approver->users[$index]->pivot->approval_required,
                        //         'sequence_no' => $approverGroup['sequence_no'],
                        //         'user_sequence_no' => $approver->users[$index]->pivot->sequence_no,
                        //         'key' => $resultData->id,
                        //         'form_id' => $formId,
                        //         'status' => $firstApprover ? 'Processing' : 'Pending',
                        //         'parallel_user_id' => isset($approver->users[$index]->parallelApprovers)
                        //             ? $approver->users[$index]->parallelApprovers
                        //             : null,
                        //     ];
                        // }
                        $parallelApprovers = $approver->users[$index]->parallelApprovers;
                        if ($parallelApprovers->isNotEmpty()) {
                            foreach ($parallelApprovers as $parallelUser) {
                                $firstParallelApprovers[] = [
                                    'workflow_id' => $workflowId,
                                    'approver_id' => $firstApproverId,
                                    'user_id' => $id,
                                    'approval_required' => $approver->users[$index]->pivot->approval_required,
                                    'sequence_no' => $approverGroup['sequence_no'],
                                    'user_sequence_no' => $approver->users[$index]->pivot->sequence_no,
                                    'key' => $resultData->id,
                                    'form_id' => $formId,
                                    'status' => $firstApprover ? 'Processing' : 'Pending',
                                    'parallel_user_id' => $parallelUser->id,
                                ];
                            }
                        }
                    }
                }
                $userIds = [];
            }
        }

        if (!empty($approvalStatuses)) {
            $allApproversSkipped = false;
        }

        /* Subscribers start */
        // $subscriberIds = array_column($defined, 'subscriber_id');

        // $subscribersData = Subscriber::with('users:id,name,email,employee_no')
        //     ->whereIn('id', $subscriberIds)->get();


        // if (!is_null($subscribersData)) {
        //     $subscriberUserIds = $subscribersData->pluck('users')->flatten()->pluck('id')->toArray();
        // }

        // $subscribers = [];

        // foreach ($subscribersData as $subscriber) {
        //     foreach ($subscriber->users as $user) {
        //         $subscribers[] = [
        //             'subscriber_id' => $subscriber->id,
        //             'user_id' => $user->id,
        //             'key' => $resultData->id,
        //             'form_id' => $formId,
        //             'email' => $user->email,
        //             'name' => $user->name,
        //             'employee_no' => $user->employee_no,
        //         ];
        //     }
        // }
        /* Subscribers End */

        $parallelApproverIds = [];

        if (!empty($approvalStatuses)) {
            // $filteredApprovers = array_filter($firstParallelApprovers, function ($approver) {
            //     return $approver['parallel_user_id']->isNotEmpty();
            // });
            // $filteredApprovers = array_filter($firstParallelApprovers, function ($approver) {
            //     return isset($approver['parallel_user_id']);
            // });
            // foreach ($filteredApprovers as $filteredApprover) {
            //     $parallelApproverIds = array_merge($parallelApproverIds, $filteredApprover['parallel_user_id']->pluck('id')->toArray());
            // }
            $parallelApproverIds = array_column($firstParallelApprovers, 'parallel_user_id');

            try {
                ApprovalStatus::insert($approvalStatuses);
                $allApproved = ApprovalStatus::where('form_id', $formId)
                    ->where('key', $resultData->id)
                    ->where('status', '<>', 'Approved')
                    ->exists();

                if (!$allApproved) {
                    $resultData->update(['status' => 'Approved']);
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            dispatch(new SendApprovalEmailJob($approvalStatuses));
            // if (!empty($subscribers)) {
            //     dispatch(new SendSubscriberEmailJob($subscribers));
            // }
            if (!empty($firstParallelApprovers)) {
                dispatch(new SendApprovalEmailToParallelApproverJob($firstParallelApprovers));
            }

            return [
                'resultData' => $resultData,
                'approverIds' => $allUserIds,
                // 'subscriberIds' => $subscriberUserIds,
                'parallelApproverIds' => $parallelApproverIds,
                'created_by' => Auth::user()->id,
            ];
        }

        if ($allApproversSkipped) {
            $resultData->update(['status' => 'Approved']);
        }

        // // \Log::info(json_encode($allApproversSkipped). ' $allApproversSkipped');
        // // dd($resultData[status]);
        // // $data = $resultData->toArray();
        // // dd($resultData->update([$resultData->status => (string) 'Approved']));
        // if ($allApproversSkipped === true) {
        //     $resultData->update(['status' => 'Approved']);
        // }

        return [
            'resultData' => $resultData,
            'approverIds' => $allUserIds,
            // 'subscriberIds' => $subscriberUserIds,
            'parallelApproverIds' => $parallelApproverIds,
            'created_by' => Auth::user()->id,
        ];
    }

    public static function approvalConditions($data, $formId, $conditionId, $sequence_no, $approver_id)
    {
        switch ($conditionId) {
            case 1:
                return ($data->change_significance == 'Major') ? true : false;
            case 2:
                return ($data->change_significance == 'Minor') ? true : false;
            case 3:
                return ($data->location_id == 2) ? true : false;
            case 4:
                return $data && $data->created_at >= '2024-01-01' && $data->created_at <= '2024-12-31';
            case 5:
                $equipmentTotal = $data->equipmentRequests->sum('total');
                $softwareTotal = $data->softwareRequests->sum('total');
                $serviceTotal = $data->serviceRequests->sum('total');
                $totalSum = $equipmentTotal + $softwareTotal + $serviceTotal;

                if ($totalSum >= 1000000 && $totalSum <= 2000000) {
                    return true;
                }
                return false;
            case 6:
                $equipmentTotal = $data->equipmentRequests->sum('total');
                $softwareTotal = $data->softwareRequests->sum('total');
                $serviceTotal = $data->serviceRequests->sum('total');
                $totalSum = $equipmentTotal + $softwareTotal + $serviceTotal;

                if ($totalSum >= 1 && $totalSum < 1000000) {
                    return true;
                }
                return false;
            case 7:
                return true;

            case 8:
                $nonApprovedRecords = ApprovalStatus::where('form_id', 4)
                    ->where('key', 160)
                    ->where(function ($query) {
                        $query->whereNull('condition_id')
                            ->orWhere('condition_id', '<>', 8);
                    })
                    ->where('status', '<>', 'Approved')
                    ->exists();

                return !$nonApprovedRecords ? ConditionCRF::execute($formId, $data, $conditionId) : false;
            case 9:
                if (!ApprovalStatus::where('form_id', 4)
                    ->where('key', $data->id)->where('user_id', Auth::user()->id)->where('condition_id', 9)->exists()) {
                    return true;
                }
                if (
                    $data->status == 'Pending' &&
                    $data->equipmentRequests()?->whereNull('asset_details')->exists() &&
                    $data->softwareRequests()?->whereNull('asset_details')->exists() &&
                    $data->serviceRequests()?->whereNull('asset_details')->exists()
                ) {
                    return true;
                }
                $hasPurchased = $data->equipmentRequests()
                    ->whereJsonContains('asset_details', ['action' => 'Purchase'])->exists()
                    || $data->softwareRequests()
                    ->whereJsonContains('asset_details', ['action' => 'Purchase'])->exists()
                    || $data->serviceRequests()
                    ->whereJsonContains('asset_details', ['action' => 'Purchase'])->exists();

                if (!$hasPurchased) {
                    ApprovalStatus::where('form_id', 4)
                        ->where('key', $data->id)
                        ->where('status', '<>', 'Approved')
                        ->update([
                            'status' => 'Approved',
                            'status_at' => now(),
                            'responded_by' => Auth::user()->id,
                            'reason' => 'Action: Purchase'
                        ]);
                    $data->update(['status' => 'Approved']);
                }
                return true;
            case 10:
                $category = SoftwareCategory::where('name', 'Sap Internal')->first();
                if (!$category) {
                    return false;
                }
                return ($data->software_category_id == $category->id) ? true : false;
            case 11:
                $category = SoftwareCategory::where('name', 'Sap Sales Group')->first();
                if (!$category) {
                    return false;
                }
                return ($data->software_category_id == $category->id) ? true : false;
            case 12:
                $updateStatus = function () use ($formId, $data) {
                    $model = FormEnum::getModelById($formId);

                    $isFirstApproval = !ApprovalStatus::where('form_id', $formId)
                        ->where('key', $data->id)
                        ->exists();

                    if ($isFirstApproval) {
                        $modelInstance = $model::withoutGlobalScope(FormDataAccessScope::class)->find($data->id);
                        if ($modelInstance) {
                            return $modelInstance->update(['status' => 'Approved']);
                        }
                    }

                    // Check if all approvals are complete
                    $allApproved = !ApprovalStatus::where('form_id', $formId)
                        ->where('key', $data->id)
                        ->where('status', '!=', 'Approved')
                        ->exists();

                    if ($allApproved) {
                        $modelInstance = $model::withoutGlobalScope(FormDataAccessScope::class)->find($data->id);
                        if ($modelInstance) {
                            $modelInstance->update(['status' => 'Approved']);
                            Log::info('Model updated: ' . $modelInstance);
                        } else {
                            Log::error('Model not found for update. formId: ' . $formId . ', dataId: ' . $data->id);
                        }
                    }

                    return null; // Explicitly return null if no update is performed
                };

                if (DB::transactionLevel() > 0) {
                    DB::afterCommit($updateStatus);
                } else {
                    $updateStatus();
                }
                // dispatch(new UpdateApprovalStatusJob($formId, $data->id, $approver_id, $sequence_no, Auth::user()->id, $data->workflow_id));
            default:
                return false;
        }
    }

    public static function validateUserAssignedTask($formId, $key): bool
    {
        $form = Form::findOrFail($formId);

        return AssignTask::where('assignable_type', $form->identity)
            ->where('assignable_id', $key)
            ->whereHas('assignTaskTeams', function ($query) {
                $query->where('member_id', Auth::user()->id);
            })->exists();
    }

    public function generateReferenceNumber(mixed $modelName)
    {
        $year = Carbon::now()->year;
        $prefix = self::generateSlug($modelName);

        $lastSeqNo = $modelName::withoutGlobalScope(FormDataAccessScope::class)
            // ->where('sequence_no', 'LIKE', "$prefix-$year-%")
            ->orderBy('id', 'desc')
            ->first('sequence_no');

        if ($lastSeqNo && !is_null($lastSeqNo->sequence_no)) {
            $number = (int) Str::afterLast($lastSeqNo->sequence_no, '-');
            $number++;

            $sequenceNumber = $prefix . '-' . $year . '-' . str_pad($number, 2, '0', STR_PAD_LEFT);

            // Ensure the sequence number is unique
            while ($modelName::where('sequence_no', $sequenceNumber)->exists()) {
                $number++;
                $sequenceNumber = $prefix . '-' . $year . '-' . str_pad($number, 2, '0', STR_PAD_LEFT);
            }
        } else {
            // If there is no sequence number for the current year, start with 01
            $sequenceNumber = $prefix . '-' . $year . '-' . '01';
        }

        return $sequenceNumber;
    }


    public static function generateSlug(string $class)
    {
        $lastPart = substr(strrchr($class, '\\'), 1);

        if (strtoupper($lastPart) === $lastPart) {
            $lastPart = strtolower($lastPart);
        } else {
            // Step 3: Skip the first capital letter and add a hyphen before every subsequent capital letter, then convert it to lowercase
            $lastPart = preg_replace_callback('/([A-Z])/', function ($matches) {
                static $first = true;
                if ($first) {
                    $first = false;
                    return strtolower($matches[0]);
                } else {
                    return '-' . strtolower($matches[0]);
                }
            }, $lastPart);
        }
        return $lastPart;
    }
}
