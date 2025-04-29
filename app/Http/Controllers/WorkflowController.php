<?php

namespace App\Http\Controllers;

use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use App\Models\ApprovalStatus;
use App\Models\Workflow;
use App\Services\GlobalFormService;
use App\Services\WorkflowDeleteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\WorkflowNames;
use App\Events\AssignPermissionToUsers;
use App\Http\Requests\UpdateInitiatedWorkflowRequest;
use App\Http\Requests\WorkflowRequest;
use App\Http\Resources\FormResource;
use App\Http\Resources\WorkflowResource;
use App\Models\Approver;
use App\Models\Form;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\WorkflowInitiatorField;
use App\Models\WorkflowSubscriberApprover;
use App\Services\WorkflowService;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        // return $workflowService->index();
        $result =  Workflow::latest()->get();
        try {
            return Helper::sendResponse(WorkflowResource::collection($result), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function workflow_type(): JsonResponse
    {
        try {
            $forms = WorkflowNames::toArray();
            return Helper::sendResponse($forms, 'Success');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(WorkflowRequest $request): JsonResponse
    {
        try {
            // dd($request);
            if (isset($request->form_id)) {
                $validated = $request->validated();
                $form = Form::find($validated['form_id']);
                if (!$form) {
                    return Helper::sendError('Form not found.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (isset($validated['workflowSubscribersApprovers'])) {
                    $sequenceNo = array_column($validated['workflowSubscribersApprovers'], 'sequence_no');

                    // Ensure all sequence numbers are integers and positive
                    if (!empty(array_filter($sequenceNo, fn($seq) => !is_int($seq) || $seq < 1))) {
                        return Helper::sendError('Sequence numbers must be positive integers.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Check for duplicate sequence numbers
                    if (count($sequenceNo) !== count(array_unique($sequenceNo))) {
                        return Helper::sendError('Sequence numbers must be unique.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Check if sequence numbers form a consecutive range starting from 1
                    sort($sequenceNo);
                    $expectedSequence = range(1, count($sequenceNo));
                    if ($sequenceNo !== $expectedSequence) {
                        return Helper::sendError('Sequence numbers must be consecutive starting from 1 without gaps.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                }

                $name = ($validated['key_one'] ?? '0') . '_' . ($validated['key_two'] ?? '0') .
                    '_' . ($validated['key_three'] ?? '0') .
                    '_' . ($validated['key_four'] ?? '0') .
                    '_' . ($validated['key_five'] ?? '0');

                if (is_array($validated['initiator_id'])) {
                    $workflows = [];
                    $workflowInitiatorFields = [];
                    $workflowSubscribersApprovers = [];

                    foreach ($validated['initiator_id'] as $initiatorId) {
                        $userName = User::findOrFail($initiatorId, ['employee_no']);

                        // Create a new workflow for each initiator_id
                        $workflow = Workflow::create([
                            'name' => $userName->employee_no . '_' . $name,
                            'created_by_id' => Auth::user()->id,
                            'callback_id' => $validated['callback_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $workflows[] = $workflow;
                        // Create a new workflow initiator field for each initiator_id
                        $workflowInitiatorField = WorkflowInitiatorField::create([
                            'workflow_id' => $workflow->id,
                            'initiator_id' => $initiatorId,
                            'form_id' => $validated['form_id'],
                            'key_one' => $validated['key_one'] ?? null,
                            'key_two' => $validated['key_two'] ?? null,
                            'key_three' => $validated['key_three'] ?? null,
                            'key_four' => $validated['key_four'] ?? null,
                            'key_five' => $validated['key_five'] ?? null,
                            'initiator_field_one_id' => $form->initiator_field_one_id,
                            'initiator_field_two_id' => $form->initiator_field_two_id,
                            'initiator_field_three_id' => $form->initiator_field_three_id,
                            'initiator_field_four_id' => $form->initiator_field_four_id,
                            'initiator_field_five_id' => $form->initiator_field_five_id,
                        ]);
                        $workflowInitiatorFields[] = $workflowInitiatorField;

                        if (isset($validated['workflowSubscribersApprovers'])) {
                            // Create new workflow subscribers/approvers for each initiator_id
                            foreach ($validated['workflowSubscribersApprovers'] as $approverData) {
                                $workflowSubscribersApprovers[] = [
                                    'workflow_id' => $workflow->id,
                                    'approver_id' => $approverData['approver_id'],
                                    'subscriber_id' => $approverData['subscriber_id'] ?? null,
                                    'approval_condition' => $approverData['approval_condition'] ?? null,
                                    'sequence_no' => $approverData['sequence_no'],
                                    'editable' => $approverData['editable'],
                                ];
                            }
                        }
                    }
                    WorkflowSubscriberApprover::insert($workflowSubscribersApprovers);

                    return Helper::sendResponse([
                        'workflows' => $workflows,
                        'initiator_fields' => $workflowInitiatorFields,
                    ], 'Successfully Added', 201);
                } else {
                    return Helper::sendError("initiator_id should be an array.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            } else {
                return Helper::sendError("Please select a valid form first.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            // $workflow = Workflow::with(
            //     'workflowSubscribersApprovers',
            //     'approvalStatuses',
            //     'workflowInitiatorField',
            //     'created_by',
            //     'approvers',
            //     'subscribers'
            // )->findOrFail($id);

            // $responseData = [
            //     'id' => $workflow->id,
            //     'name' => $workflow->name,
            //     'created_by' => [
            //         'id' => $workflow->created_by->id,
            //         'name' => $workflow->created_by->name
            //     ],
            //     'workflow_initiator_field' => [
            //         'key_one' => $workflow->workflowInitiatorField->key_one,
            //         'key_two' => $workflow->workflowInitiatorField->key_two,
            //         'key_three' => $workflow->workflowInitiatorField->key_three,
            //         'key_four' => $workflow->workflowInitiatorField->key_four,
            //         'key_five' => $workflow->workflowInitiatorField->key_five,
            //     ],
            //     'form' => $workflow->workflowInitiatorField->form->only('id', 'name') ?: null,
            //     'workflow_initiator' => $workflow->workflowInitiatorField->workflowInitiator->only('id', 'name') ?: null,
            //     'workflow_subscribers_approvers' => $workflow->workflowSubscribersApprovers->map(function ($wsa, $index) {
            //         return [
            //             $index =>
            //                 [
            //                     'id' => $wsa->id,
            //                     'approval_condition' => $wsa->approval_condition,
            //                     'sequence_no' => $wsa->sequence_no,
            //                     // 'editable' => $wsa->editable,
            //                     'approver' => [
            //                         'id' => $wsa->approver->id,
            //                         'name' => $wsa->approver->name,
            //                     ],
            //                     'subscriber' => [
            //                         'id' => $wsa->subscriber->id,
            //                         'name' => $wsa->subscriber->name,
            //                     ],
            //                 ]
            //         ];
            //     })
            // ];
            $result = Workflow::find($id);
            return Helper::sendResponse(new WorkflowResource($result), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $workflowId): JsonResponse
    {
        try {
            $this->validate($request, [
                'workflowSubscribersApprovers' => 'required|array|min:1',
                'workflowSubscribersApprovers.*.approver_id' => 'required|exists:approvers,id',
                'workflowSubscribersApprovers.*.subscriber_id' => 'nullable|exists:subscribers,id',
                'workflowSubscribersApprovers.*.approval_condition' => 'nullable|exists:conditions,id',
                'workflowSubscribersApprovers.*.sequence_no' => 'required|integer|min:1',
                'workflowSubscribersApprovers.*.editable' => 'nullable|boolean',
            ]);
            $sequence_no = $request->input('sequence_no');
            (int) $sequence_no;
            $sequenceNo = array_column($request->input('workflowSubscribersApprovers'), 'sequence_no');

            // Check for duplicate sequence numbers in request data
            if (count($sequenceNo) !== count(array_unique($sequenceNo))) {
                return Helper::sendError('Sequence numbers must be unique.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check if sequence numbers form a consecutive range starting from 1
            $sortedSequence = $sequenceNo;
            sort($sortedSequence);
            $expectedSequence = range(1, count($sortedSequence));
            if ($sortedSequence !== $expectedSequence) {
                return Helper::sendError('Sequence numbers must be consecutive starting from 1 without gaps.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $workflow = Workflow::with('workflowSubscribersApprovers')->find($workflowId);

            if ($request->has('workflowSubscribersApprovers')) {
                $workflowSubscribersApprovers = [];

                foreach ($request->input('workflowSubscribersApprovers') as $approverData) {
                    $workflowSubscribersApprovers[] = [
                        'workflow_id' => $workflow->id,
                        'approver_id' => $approverData['approver_id'],
                        'subscriber_id' => $approverData['subscriber_id'] ?? null,
                        'approval_condition' => $approverData['approval_condition'] ?? null,
                        'sequence_no' => $approverData['sequence_no'],
                        'editable' => $approverData['editable'],
                    ];
                }

                DB::transaction(function () use ($workflowSubscribersApprovers, $workflow) {
                    WorkflowSubscriberApprover::where('workflow_id', $workflow->id)->delete();
                    WorkflowSubscriberApprover::insert($workflowSubscribersApprovers);
                });
            }

            return Helper::sendResponse(new WorkflowResource($workflow), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // public function update(Request $request, $workflowId): JsonResponse
    // {
    //     try {
    //         $this->validate($request, [
    //             'workflowSubscribersApprovers' => 'required|array|min:1',
    //             'workflowSubscribersApprovers.*.approver_id' => 'required|exists:approvers,id',
    //             'workflowSubscribersApprovers.*.subscriber_id' => 'nullable|exists:subscribers,id',
    //             'workflowSubscribersApprovers.*.approval_condition' => 'nullable|exists:conditions,id',
    //             'workflowSubscribersApprovers.*.sequence_no' => [
    //                 'required',
    //                 'integer',
    //                 Rule::unique('workflow_subscribers_approvers', 'sequence_no')
    //                     ->where(function ($query) use ($request) {
    //                         return $query->where('workflow_id', $request->input('workflow_id'))
    //                             ->where('sequence_no', $request->input('workflowSubscribersApprovers.*.sequence_no'));
    //                     }),
    //             ],
    //             'workflowSubscribersApprovers.*.editable' => 'nullable|boolean',
    //         ]);

    //         $workflow = Workflow::with('workflowSubscribersApprovers')->find($workflowId);

    //         if ($request->has('workflowSubscribersApprovers')) {
    //             $workflowSubscribersApprovers = [];

    //             foreach ($request->input('workflowSubscribersApprovers') as $approverData) {
    //                 $workflowSubscribersApprovers[] = [
    //                     'workflow_id' => $workflow->id,
    //                     'approver_id' => $approverData['approver_id'],
    //                     'subscriber_id' => $approverData['subscriber_id'] ?? null,
    //                     'approval_condition' => $approverData['approval_condition'] ?? null,
    //                     'sequence_no' => $approverData['sequence_no'],
    //                     'editable' => $approverData['editable'],
    //                 ];
    //             }

    //             DB::transaction(function () use ($workflowSubscribersApprovers, $workflow) {
    //                 WorkflowSubscriberApprover::where('workflow_id', $workflow->id)->delete();
    //                 WorkflowSubscriberApprover::insert($workflowSubscribersApprovers);
    //             });
    //         }
    //         return Helper::sendResponse(new WorkflowResource($workflow), 'Success', 200);
    //     } catch (\Exception $e) {
    //         // \Log::error($e);
    //         return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }
    // }

    public function updateInitiatedWorkflow(UpdateInitiatedWorkflowRequest $request): JsonResponse
    {
        try {
            $workflowSubscribers = [];
            $formId = $request->input('form_id');
            $key = $request->input('key');
            $model = FormEnum::getModelById($formId);
            $record = $model::findOrFail($key);

            $workflowId = (int) $record->workflow_id;
            if ($record->status == 'Approved') {
                $workflowApproversSubscribers = [];
                foreach ($request->input('approverSubscribers') as $approverSubscriber) {
                    $approverId = (int) $approverSubscriber['approver_id'];
                    $subscriberId = (int) $approverSubscriber['subscriber_id'];

                    $workflowApproversSubscribers[] = [
                        'id' => $key,
                        'workflow_id' => $workflowId,
                        'approver_id' => $approverId,
                        'subscriber_id' => $subscriberId,
                        'sequence_no' => (int) $approverSubscriber['sequence_no'],
                        'approval_condition' => $approverSubscriber['approval_condition'],
                        'editable' => $approverSubscriber['editable'],
                    ];
                }

                DB::transaction(function () use ($workflowApproversSubscribers, $formId, $workflowId, $key, $record, $model): void {
                    $record->update(['status' => 'Pending']);
                    $result = GlobalFormService::processApprovals($record, $workflowApproversSubscribers, $workflowId, $formId);
                    $approverUserIds = Approver::whereIn('id', $result['approverIds'])->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();
                    $subscriberUserIds = Subscriber::whereIn('id', $result['subscriberIds'])->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();

                    $mergedArray = array_unique(array_merge($approverUserIds, $subscriberUserIds));

                    event(new AssignPermissionToUsers($mergedArray, $model, $key));
                });
                return Helper::sendResponse($workflowSubscribers, 'Approval statuses saved successfully!', 201);
            } else {

                foreach ($request->input('approverSubscribers') as $approverSubscriber) {
                    $approverId = (int) $approverSubscriber['approver_id'];
                    if (isset($approverSubscriber['subscriber_id'])) {
                        $subscriberId = (int) $approverSubscriber['subscriber_id'];
                    } else {
                        $subscriberId = null;
                    }


                    $approver = Approver::with(['users' => function ($query) {
                        $query->select('id', 'name');
                    }])->find($approverId);

                    foreach ($approver->users as $user) {
                        $workflowApprovers[] = [
                            'workflow_id' => $workflowId,
                            'approver_id' => $approverId,
                            'user_id' => $user->id,
                            'approval_required' => (int) $user->pivot->approval_required,
                            'sequence_no' => (int) $user->pivot->sequence_no,
                            'condition_id' => $approverSubscriber['approval_condition'],
                            'form_id' => (int) $formId,
                            'key' => (int) $key,
                            'reason' => null,
                            'status' => 'Pending',
                            'status_at' => null,
                            'responded_by' => null,
                            'editable' => (bool) $approverSubscriber['editable'],
                        ];
                    }
                    if ($subscriberId) {
                        $subscribers = Subscriber::with(['users' => function ($query) {
                            $query->select('id', 'name');
                        }])->find($subscriberId);

                        foreach ($subscribers->users as $user) {
                            $workflowSubscribers[] = [
                                'id' => $user->id,
                                'user_id' => $user->name,
                            ];
                        }
                    }
                }

                // dd($workflowApprovers);
                $approverIds = array_column($request->input('approverSubscribers'), 'approver_id');
                // $subscriberIds = array_column($request->input('approverSubscribers'), 'subscriber_id');

                if (isset($request->input('approverSubscribers')[0]['subscriber_id'])) {
                    $subscriberIds = array_column($request->input('approverSubscribers'), 'subscriber_id');
                } else {
                    $subscriberIds = [];
                }

                $approverUserIds = Approver::whereIn('id', $approverIds)->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();

                if (!empty($subscriberIds)) {
                    $subscriberUserIds = Subscriber::whereIn('id', $subscriberIds)->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();
                } else {
                    $subscriberUserIds = [];
                }

                // $subscriberUserIds = Subscriber::whereIn('id', $subscriberIds)->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();


                DB::transaction(function () use ($workflowApprovers, $key, $model, $approverUserIds, $subscriberUserIds): void {
                    DB::table('approval_statuses')->insert($workflowApprovers);
                    $mergedArray = array_unique(array_merge($approverUserIds, $subscriberUserIds));
                    event(new AssignPermissionToUsers($mergedArray, $model, $key));
                });

                // foreach ($request->input('approverSubscribers') as $approverSubscriber) {
                //     $approverId = (int) $approverSubscriber['approver_id'];
                //     $subscriberId = (int) $approverSubscriber['subscriber_id'];


                //     $approver = Approver::with(['users' => function ($query) {
                //         $query->select('id', 'name');
                //     }])->find($approverId);

                //     foreach ($approver->users as $user) {
                //         $workflowApprovers[] = [
                //             'workflow_id' => $workflowId,
                //             'approver_id' => $approverId,
                //             'user_id' => $user->id,
                //             'approval_required' => (int) $user->pivot->approval_required,
                //             'sequence_no' => (int) $user->pivot->sequence_no,
                //             'condition_id' => $approverSubscriber['approval_condition'],
                //             'form_id' => (int) $formId,
                //             'key' => (int) $key,
                //             'reason' => null,
                //             'status' => 'Pending',
                //             'status_at' => null,
                //             'responded_by' => null,
                //             'editable' => (bool) $approverSubscriber['editable'],
                //         ];
                //     }
                //     $subscribers = Subscriber::with(['users' => function ($query) {
                //         $query->select('id', 'name');
                //     }])->find($subscriberId);

                //     foreach ($subscribers->users as $user) {
                //         $workflowSubscribers[] = [
                //             'id' => $user->id,
                //             'user_id' => $user->name,
                //         ];
                //     }
                // }

                // $approverIds = array_column($request->input('approverSubscribers'), 'approver_id');
                // $subscriberIds = array_column($request->input('approverSubscribers'), 'subscriber_id');
                // $approverUserIds = Approver::whereIn('id', $approverIds)->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();
                // $subscriberUserIds = Subscriber::whereIn('id', $subscriberIds)->with('users')->get()->pluck('users')->flatten()->pluck('id')->toArray();


                // DB::transaction(function () use ($workflowApprovers, $key, $model, $approverUserIds, $subscriberUserIds): void {
                //     DB::table('approval_statuses')->insert($workflowApprovers);
                //     $mergedArray = array_unique(array_merge($approverUserIds, $subscriberUserIds));
                //     event(new AssignPermissionToUsers($mergedArray, $model, $key));
                // });
            }
            return Helper::sendResponse($workflowApprovers, 'Approval statuses saved successfully!', 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function deleteWorkflowGroup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'form_id' => 'required|integer|min:1',
                'key' => 'required|integer|min:1',
                'approver_id' => 'required|integer|min:1',
            ]);

            $deleted = DB::transaction(function () use ($validated) {
                $records = ApprovalStatus::where('form_id', operator: $validated['form_id'])
                    ->where('key', operator: $validated['key'])
                    ->where('approver_id', operator: $validated['approver_id'])
                    ->lockForUpdate()
                    ->get();

                if ($records->isEmpty()) {
                    return 'not_found';
                }

                if ($records->every(function ($record): bool {
                    return ($record->status === 'Pending');
                })) {
                    ApprovalStatus::where('form_id', $validated['form_id'])
                        ->where('key', $validated['key'])
                        ->where('approver_id', $validated['approver_id'])
                        ->delete();
                    return true;
                }

                return false;
            });

            if ($deleted === true) {
                return Helper::sendResponse([], 'Successfully deleted', 200);
            } elseif ($deleted === 'not_found') {
                return Helper::sendError('No records found', [], 404);
            } else {
                return Helper::sendError('Cannot delete. Not all records are pending.', [], 422); // or 400
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    public function destroy(int $workflowId, WorkflowDeleteService $workflowDeleteService): JsonResponse
    {
        try {
            return $workflowDeleteService->deleteWorkflow($workflowId);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
