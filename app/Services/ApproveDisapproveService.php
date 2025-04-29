<?php

namespace App\Services;

use App\Enums\FormEnum;
use App\Events\AssignPermissionToUsers;
use App\Http\Helpers\Helper;
use App\Jobs\SendApprovalEmailJob;
use App\Jobs\SendSubscriberEmailJob;
use App\Models\ApprovalStatus;
use App\Models\Form;
use App\Models\ParallelApprover;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\Subscriber;
use Auth;
use Carbon\Carbon;
use Google\Service\CloudCommercePartnerProcurementService\Approval;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class ApproveDisapproveService
{
    protected $identityModel;

    public function storeApproveDisapprove(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validatedData = $request->validate([
                    'form_id' => ['required', 'integer', 'exists:forms,id'],
                    'reason' => [
                        'required_if:status,Disapproved,Return',
                        'string',
                        'min:2',
                        'max:255',
                    ],
                    'status' => [
                        'required',
                        Rule::in(["Approved", "Disapproved", "Return"]),
                    ],
                ]);

                $formId = $validatedData['form_id'];
                $status = ucfirst($validatedData['status']);
                $reason = $request->reason ?? '';

                $form = Form::find($formId);
                $identityClassName = $form->identity;
                if (!$identityClassName || !class_exists($identityClassName)) {
                    throw new \Exception("Identity class not found");
                }

                $keyValidation = $request->validate([
                    'key' => ['required', Rule::exists($form->identity, 'id')]
                ]);

                $key = $keyValidation['key'];

                $record = ApprovalStatus::where('form_id', $formId)
                    ->where('key', $key)
                    ->where('user_id', Auth::user()->id)
                    ->where('status', 'Processing')
                    ->first();

                if (!$record) {
                    return Helper::sendResponse('', 'No status found to be updated.', 404);
                }

                $updateData = [
                    'reason' => $reason,
                    'status' => $status,
                    'status_at' => Carbon::now(),
                    'responded_by' => Auth::user()->id
                ];

                ApprovalStatus::where('form_id', $formId)
                    ->where('key', $key)
                    ->where('user_id', Auth::user()->id)
                    ->where('status', 'Processing')
                    ->update($updateData);


                if ($status == "Disapproved" || $status == "Return") {
                    $identityModel = $identityClassName::withoutGlobalScope(FormDataAccessScope::class)->find($key);

                    if ($identityModel) {
                        $identityModel->update(['status' => $status]);
                    }

                    activity()
                        ->performedOn($identityModel)
                        ->createdAt(now())
                        ->event($status)
                        ->log($reason);

                    ApprovalStatus::where('form_id', $formId)
                        ->where('key', $key)
                        ->whereNot('status', 'Approved')
                        ->update(['status' => $status, 'responded_by' => DB::raw('COALESCE(responded_by, ' . Auth::user()->id . ')')]);

                    return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
                } elseif ($status == "Approved") {

                    $currentApproverId = $record['approver_id'];
                    $currentRemaining = ApprovalStatus::where('form_id', $formId)
                        ->where('key', $key)
                        ->where('approver_id', $currentApproverId)
                        ->get();


                    $identityModel = $identityClassName::withoutGlobalScope(FormDataAccessScope::class)->find($key);

                    activity()
                        ->performedOn($identityModel)
                        ->createdAt(now())
                        ->event($status)
                        ->log($reason);


                    if (!isset($identityModel->sequence_no)) {
                        $seq_no = 1;
                    } else {
                        $seq_no = $identityModel->sequence_no;
                    }

                    if ($currentRemaining->where('status', '<>', 'Approved')->count() > 0) {
                        $countApprovalRequiredZero = $currentRemaining->where('approval_required', 0)->count();

                        if ($countApprovalRequiredZero > 0) {
                            $allRecordZero = $currentRemaining->where('status', '<>', 'Approved')->where('approval_required', 0)->isEmpty();
                            $firstRecordZero = $currentRemaining->where('status', 'Approved')->where('approval_required', 0)->isNotEmpty();

                            if (!$allRecordZero && $firstRecordZero) {
                                ApprovalStatus::where('form_id', $formId)
                                    ->where('key', $key)
                                    ->where('approval_required', 0)
                                    ->where('status', '<>', 'Approved')
                                    ->where('approver_id', $currentApproverId)
                                    ->update([
                                        'status' => 'Approved',
                                        'reason' => 'Approved',
                                        'status_at' => Carbon::now(),
                                        'responded_by' => Auth::user()->id
                                    ]);

                                $countApprovalRequiredOne = $currentRemaining->where('approval_required', 1)->where('status', '<>', 'Approved')->isNotEmpty();

                                if ($countApprovalRequiredOne) {
                                    return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                                }

                                $exists = ApprovalStatus::where('form_id', $formId)
                                    ->where('key', $key)
                                    ->where('approver_id', $currentApproverId)
                                    ->where('status', '<>', 'Approved')
                                    ->exists();
                                if (!$exists) {

                                    $this->subscribersNotify($formId, $key, $currentApproverId);
                                    self::getNextApprovers($formId, $key);
                                    return self::statusToProcessing($key, $formId, $identityClassName, $status);
                                }

                                return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                            }
                            return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                        }

                        $countApprovalRequiredOne = $currentRemaining->where('approval_required', 1)->where('status', '<>', 'Approved')->isNotEmpty();

                        if ($countApprovalRequiredOne) {
                            return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                        }

                        $this->subscribersNotify($formId, $key, $currentApproverId);
                        self::getNextApprovers($formId, $key);
                        self::statusToProcessing($key, $formId, $identityClassName, $status);
                        return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                    }

                    $this->subscribersNotify($formId, $key, $currentApproverId);
                    self::getNextApprovers($formId, $key);
                    self::statusToProcessing($key, $formId, $identityClassName, $status);

                    $approvalStatuses[0]['key'] = $key;
                    $approvalStatuses[0]['form_id'] = $formId;
                    $approvalStatuses[0]['status'] = 'Approved';

                    return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                }
            }, 5);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }

    public function storeParallelApproveDisapprove(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validatedData = $request->validate([
                    'form_id' => ['required', 'integer', 'exists:forms,id'],
                    'assigned_user_id' => ['required', 'integer', 'exists:users,id'],
                    'reason' => [
                        'required_if:status,Disapproved,Return',
                        'string',
                        'min:2',
                        'max:255',
                    ],
                    'status' => [
                        'required',
                        Rule::in(["Approved", "Disapproved", "Return"]),
                    ],
                ]);

                $formId = $validatedData['form_id'];
                $status = ucfirst($validatedData['status']);
                $reason = $request->reason ?? '';

                $form = Form::find($formId);
                $identityClassName = $form->identity;
                if (!$identityClassName || !class_exists($identityClassName)) {
                    throw new \Exception("Identity class not found");
                }

                $keyValidation = $request->validate([
                    'key' => ['required', Rule::exists($form->identity, 'id')]
                ]);

                $key = $keyValidation['key'];
                $parallelUserValidate = ParallelApprover::where('user_id', $request->assigned_user_id)
                    ->where('parallel_user_id', Auth::user()->id)->exists();
                if (!$parallelUserValidate) {
                    throw new AuthorizationException('You are not authorized to perform this action.');
                }

                $record = ApprovalStatus::where('form_id', $formId)
                    ->where('key', $key)
                    ->where('user_id', $request->assigned_user_id)
                    ->where('status', 'Processing')
                    ->first();

                if (!$record) {
                    return Helper::sendResponse('', 'No status found to be updated.', 404);
                }

                $updateData = [
                    'reason' => $reason,
                    'status' => $status,
                    'status_at' => Carbon::now(),
                    'is_parallel' => 1,
                    'responded_by' => Auth::user()->id
                ];

                ApprovalStatus::where('form_id', $formId)
                    ->where('key', $key)
                    ->where('user_id', $request->assigned_user_id)
                    ->where('status', 'Processing')
                    ->update($updateData);


                if ($status == "Disapproved" || $status == "Return") {
                    $identityModel = $identityClassName::withoutGlobalScope(FormDataAccessScope::class)->with('user')->find($key);
                    if ($identityModel) {
                        $identityModel->update(['status' => $status]);
                        $createdBy = $identityModel->user ?? null;

                        activity()
                            ->performedOn($identityModel)
                            ->createdAt(now())
                            ->event($status . ' by ' . Auth::user()->name . ' as a parallel approver)')
                            ->log($reason ?? 'No reason provided');
                    }


                    ApprovalStatus::where('form_id', $formId)
                        ->where('key', $key)
                        ->whereNot('status', 'Approved')
                        ->update(['status' => $status, 'responded_by' => DB::raw('COALESCE(responded_by, ' . Auth::user()->id . ')')]);

                    return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
                } elseif ($status == "Approved") {

                    $currentApproverId = $record['approver_id'];
                    $currentRemaining = ApprovalStatus::where('form_id', $formId)
                        ->where('key', $key)
                        ->where('approver_id', $currentApproverId)
                        ->get();
                    $identityModel = $identityClassName::withoutGlobalScope(FormDataAccessScope::class)->with('user')->find($key);

                    if ($identityModel) {
                        $createdBy = $identityModel->user ?? null;

                        activity()
                            ->performedOn($identityModel)
                            ->createdAt(now())
                            ->event($status . ' by ' . Auth::user()->name . ' as a parallel approver)')
                            ->log($reason ?? 'No reason provided');
                    }


                    if (!isset($identityModel->sequence_no)) {
                        $seq_no = 1;
                    } else {
                        $seq_no = $identityModel->sequence_no;
                    }

                    if ($currentRemaining->where('status', '<>', 'Approved')->count() > 0) {
                        $countApprovalRequiredZero = $currentRemaining->where('approval_required', 0)->count();

                        if ($countApprovalRequiredZero > 0) {
                            $allRecordZero = $currentRemaining->where('status', '<>', 'Approved')->where('approval_required', 0)->isEmpty();
                            $firstRecordZero = $currentRemaining->where('status', 'Approved')->where('approval_required', 0)->isNotEmpty();

                            if (!$allRecordZero && $firstRecordZero) {
                                ApprovalStatus::where('form_id', $formId)
                                    ->where('key', $key)
                                    ->where('approval_required', 0)
                                    ->where('status', '<>', 'Approved')
                                    ->where('approver_id', $currentApproverId)
                                    ->update([
                                        'status' => 'Approved',
                                        'reason' => 'Approved',
                                        'status_at' => Carbon::now(),
                                        'is_parallel' => 1,
                                        'responded_by' => Auth::user()->id
                                    ]);

                                $countApprovalRequiredOne = $currentRemaining->where('approval_required', 1)->where('status', '<>', 'Approved')->isNotEmpty();

                                if ($countApprovalRequiredOne) {
                                    return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                                }

                                $exists = ApprovalStatus::where('form_id', $formId)
                                    ->where('key', $key)
                                    ->where('approver_id', $currentApproverId)
                                    ->where('status', '<>', 'Approved')
                                    ->exists();
                                if (!$exists) {

                                    $this->subscribersNotify($formId, $key, $currentApproverId);
                                    self::getNextApprovers($formId, $key);
                                    return self::statusToProcessing($key, $formId, $identityClassName, $status);
                                }

                                return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                            }
                            return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                        }

                        $countApprovalRequiredOne = $currentRemaining->where('approval_required', 1)->where('status', '<>', 'Approved')->isNotEmpty();

                        if ($countApprovalRequiredOne) {
                            return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                        }

                        $this->subscribersNotify($formId, $key, $currentApproverId);
                        self::getNextApprovers($formId, $key);
                        self::statusToProcessing($key, $formId, $identityClassName, $status);
                        return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                    }

                    $this->subscribersNotify($formId, $key, $currentApproverId);
                    self::getNextApprovers($formId, $key);
                    self::statusToProcessing($key, $formId, $identityClassName, $status);

                    $approvalStatuses[0]['key'] = $key;
                    $approvalStatuses[0]['form_id'] = $formId;
                    $approvalStatuses[0]['status'] = 'Approved';

                    return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $seq_no . ' successfully Approved.', 201);
                }
            }, 5);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }

    // public function storeParallelApproveDisapprove(Request $request)
    // {
    //     try {
    //         return DB::transaction(function () use ($request) {
    //             $validatedData = $request->validate([
    //                 'form_id' => ['required', 'integer', 'exists:forms,id'],
    //                 'assigned_user_id' => ['required', 'integer', 'exists:users,id'],
    //                 'status' => [
    //                     'required',
    //                     Rule::in(["Approved", "Disapproved", "Return"]),
    //                 ],
    //                 // 'reason' => ['required', 'string', 'min:2', 'max:255'],
    //                 'reason' => [
    //                     'required_if:status,Disapproved,Return',
    //                     'string',
    //                     'min:2',
    //                     'max:255',
    //                 ],
    //             ]);

    //             $formId = $validatedData['form_id'];
    //             $status = ucfirst($validatedData['status']);
    //             $reason = $request->reason ?? '';

    //             $form = Form::find($formId);
    //             $identityClassName = $form->identity;

    //             if (!$identityClassName || !class_exists($identityClassName)) {
    //                 throw new \Exception("Identity class not found");
    //             }

    //             $keyValidation = $request->validate([
    //                 'key' => ['required', Rule::exists($form->identity, 'id')]
    //             ]);

    //             $key = $keyValidation['key'];
    //             $parallelUserValidate = ParallelApprover::where('user_id', $request->assigned_user_id)
    //                 ->where('parallel_user_id', Auth::user()->id)->exists();
    //             if (!$parallelUserValidate) {
    //                 throw new AuthorizationException('You are not authorized to perform this action.');
    //             }

    //             $record = ApprovalStatus::where('form_id', $formId)
    //                 ->where('key', $key)
    //                 ->where('user_id', $request->assigned_user_id)
    //                 ->where('status', 'Processing')
    //                 ->first();

    //             if (!$record) {
    //                 return Helper::sendResponse('', 'No status found to be updated.', 404);
    //             }

    //             $updateData = [
    //                 'reason' => $reason,
    //                 'status' => $status,
    //                 'status_at' => Carbon::now(),
    //                 'responded_by' => Auth::user()->id
    //             ];

    //             ApprovalStatus::where('form_id', $formId)
    //                 ->where('key', $key)
    //                 ->where('user_id', $request->assigned_user_id)
    //                 ->where('status', 'Processing')
    //                 ->update($updateData);

    //             if ($status == "Disapproved" || $status == "Return") {
    //                 $identityModel = $identityClassName::find($key);

    //                 if ($identityModel) {
    //                     $identityModel->update(['status' => $status]);
    //                 }

    //                 ApprovalStatus::where('form_id', $formId)
    //                     ->where('key', $key)
    //                     ->whereNot('status', 'Approved')
    //                     ->update(['status' => $status, 'responded_by' => DB::raw('COALESCE(responded_by, ' . Auth::user()->id . ')')]);

    //                 return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //             } elseif ($status == "Approved") {
    //                 $currentApproverId = $record['approver_id'];
    //                 $currentRemaining = ApprovalStatus::where('form_id', $formId)
    //                     ->where('key', $key)
    //                     ->where('approver_id', $currentApproverId)
    //                     ->get();
    //                 $identityModel = $identityClassName::find($key);
    //                 if ($currentRemaining->where('status', '<>', 'Approved')->count() > 0) {
    //                     $countApprovalRequiredZero = $currentRemaining->where('approval_required', 0)->count();

    //                     if ($countApprovalRequiredZero > 0) {
    //                         $allRecordZero = $currentRemaining->where('status', '<>', 'Approved')->where('approval_required', 0)->isEmpty();
    //                         $firstRecordZero = $currentRemaining->where('status', 'Approved')->where('approval_required', 0)->isNotEmpty();

    //                         if (!$allRecordZero && $firstRecordZero) {
    //                             ApprovalStatus::where('form_id', $formId)
    //                                 ->where('key', $key)
    //                                 ->where('approval_required', 0)
    //                                 ->where('status', '<>', 'Approved')
    //                                 ->where('approver_id', $currentApproverId)
    //                                 ->update([
    //                                     'status' => 'Approved',
    //                                     'reason' => 'Approved',
    //                                     'status_at' => Carbon::now(),
    //                                     'responded_by' => Auth::user()->id
    //                                 ]);

    //                             $countApprovalRequiredOne = $currentRemaining->where('approval_required', 1)->where('status', '<>', 'Approved')->isNotEmpty();

    //                             if ($countApprovalRequiredOne) {
    //                                 return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //                             }

    //                             $exists = ApprovalStatus::where('form_id', $formId)
    //                                 ->where('key', $key)
    //                                 ->where('approver_id', $currentApproverId)
    //                                 ->where('status', '<>', 'Approved')
    //                                 ->exists();

    //                             if (!$exists) {
    //                                 self::getNextApprovers($formId, $key);
    //                                 self::statusToProcessing($key, $formId, $identityClassName, $status);
    //                             }

    //                             return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //                         }
    //                         return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //                     }

    //                     $countApprovalRequiredOne = $currentRemaining->where('approval_required', 1)->where('status', '<>', 'Approved')->isNotEmpty();

    //                     if ($countApprovalRequiredOne) {
    //                         return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //                     }

    //                     self::getNextApprovers($formId, $key);
    //                     self::statusToProcessing($key, $formId, $identityClassName, $status);
    //                     return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //                 }

    //                 self::getNextApprovers($formId, $key);
    //                 self::statusToProcessing($key, $formId, $identityClassName, $status);
    //                 $approvalStatuses[0]['key'] = $key;
    //                 $approvalStatuses[0]['form_id'] = $formId;
    //                 $approvalStatuses[0]['status'] = $status;

    //                 return Helper::sendResponse([], 'Request ' . $form->name . ' ' . $identityModel->sequence_no . ' successfully .' . $status, 201);
    //             }
    //         }, 5);
    //     } catch (\Exception $e) {
    //         return Helper::sendError($e->getMessage(), [], 422);
    //     }
    // }




    public function statusToProcessing($key, $form_id, $identityClassName, $validatedStatus)
    {
        $pendingExists = ApprovalStatus::where('form_id', $form_id)
            ->where('key', $key)
            // ->where('status', 'Pending')
            ->get()
            ->sortBy('sequence_no');

        // $allPendingAndNoneProcessing = $pendingExists->every(function ($item) {
        //     return $item->status === 'Pending';
        // }) && $pendingExists->every(function ($item) {
        //     return $item->status !== 'Processing';
        // });

        $approvalStatusArray = [];
        if ($pendingExists->where('status', 'Processing')->isEmpty() && $pendingExists->where('status', 'Pending')->isNotEmpty()) {

            $firstApproverId = $pendingExists->where('status', 'Pending')->first()->approver_id;
            ApprovalStatus::where('form_id', $form_id)
                ->where('key', $key)
                ->where('status', 'Pending')
                ->where('approver_id', $firstApproverId)
                ->update([
                    'status' => 'Processing'
                ]);

            $updatedRecords = ApprovalStatus::where('form_id', $form_id)
                ->where('key', $key)
                ->where('status', 'Processing')
                ->get();

            foreach ($updatedRecords as $record) {
                $approvalStatusArray[] = [
                    'workflow_id' => $record->workflow_id,
                    'approver_id' => $record->approver_id,
                    'user_id' => $record->user_id,
                    'approval_required' => $record->approval_required,
                    'sequence_no' => $record->sequence_no,
                    'form_id' => $form_id,
                    'key' => $key,
                    'status' => $record->status,
                ];
            }
            dispatch(new SendApprovalEmailJob($approvalStatusArray));
            return Helper::sendResponse($approvalStatusArray, "Status successfully updated.", 201);
        }
        if ($pendingExists->where('status', 'Processing')->isNotEmpty()) {
            return Helper::sendResponse([], "Status successfully updated.", 201);
        }
        $identityModel = $identityClassName::withoutGlobalScope(FormDataAccessScope::class)->find($key);

        if ($identityModel) {
            $identityModel->update(['status' => $validatedStatus]);
        }
        return Helper::sendResponse([], "Status successfully updated.", 201);
    }

    protected function getNextApprovers($form_id, $key)
    {
        $pendingExists = ApprovalStatus::where('form_id', $form_id)
            ->where('key', $key)
            ->where('status', 'Pending')
            ->get()
            ->sortBy('sequence_no');

        if ($pendingExists->isNotEmpty()) {

            $globalFormService = new GlobalFormService;

            $firstApproverId = $pendingExists[0]?->approver_id;
            $conditionId = $pendingExists[0]?->condition_id;

            if ($firstApproverId === null || $conditionId === null) {
                return;
            }
            $groupUserIds = $pendingExists->where('approver_id', $firstApproverId)->pluck('user_id')->toArray();

            $parallelUserIds = DB::table('approver_location_parallel_user')->whereIn('user_id', $groupUserIds)
                ->pluck('parallel_user_id')->toArray();
            $allUserIds = array_unique(array_merge($parallelUserIds, $groupUserIds));

            event(new AssignPermissionToUsers($allUserIds, FormEnum::getModelById($form_id), $key));

            if (!is_null($conditionId)) {
                $form = Form::find($form_id);
                $data = $form->identity::find($key);
                // ($resultData, $formId, $conditionId, $sequence_no, $approver_id)
                $matched = $globalFormService->approvalConditions($data, $form->id, $conditionId, $pendingExists[0]->sequence_no, $firstApproverId);
                if (!$matched) {
                    ApprovalStatus::where('form_id', $form_id)
                        ->where('key', $key)
                        ->where('approver_id', $firstApproverId)
                        ->update([
                            'status' => 'Approved',
                            'reason' => 'Approved because the condition was not matched.',
                            'status_at' => Carbon::now(),
                            'responded_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }

    public function subscribersNotify($formId, $key, $approverId): void
    {
        $subscriberId = ApprovalStatus::where('form_id', $formId)
            ->where('key', $key)
            ->where('approver_id', $approverId)
            ->value('subscriber_id');

        if ($subscriberId) {
            $subscriber = Subscriber::with('users:id,name,email,employee_no')->find($subscriberId);

            if ($subscriber) {
                $subscribers = [];

                foreach ($subscriber->users as $user) {
                    $subscribers[] = [
                        'subscriber_id' => $subscriber->id,
                        'user_id' => $user->id,
                        'key' => $key,
                        'form_id' => $formId,
                        'email' => $user->email,
                        'name' => $user->name,
                        'employee_no' => $user->employee_no,
                    ];
                }

                if (!empty($subscribers)) {
                    dispatch(new SendSubscriberEmailJob($subscribers));
                }
            }
        }
    }
}
