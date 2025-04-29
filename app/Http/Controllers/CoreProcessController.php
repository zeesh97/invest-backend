<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\ApprovalStatus;
use App\Models\Form;
use App\Services\ApproveDisapproveService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CoreProcessController extends Controller
{
    public function approveDisapprove(Request $request, ApproveDisapproveService $approveDisapprove)
    {
        try {
            return $approveDisapprove->storeApproveDisapprove($request);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }

    public function parallelApproveDisapprove(Request $request, ApproveDisapproveService $approveDisapprove)
    {
        try {
            return $approveDisapprove->storeParallelApproveDisapprove($request);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }

    public function getCurrentWorkflow(Request $request)
    {
        try {
            $validated = $request->validate([
                'form_id' => 'required|exists:forms,id',
                'key' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $form = Form::find($request->input('form_id'));
                        if ($form && !$form->identity::where('id', $value)->exists()) {
                            $fail('The selected key is invalid for this form.');
                        }
                    },
                ],
            ]);

            $workflows = ApprovalStatus::select(['form_id', 'approver_id', 'key', 'sequence_no', 'user_id', 'workflow_id', 'status'])
                ->with([
                    'approver:id,name',
                    'user:id,name',
                    'form:id,name'
                ])
                ->where('form_id', $validated['form_id'])
                ->where('key', $validated['key'])
                ->orderBy('sequence_no')
                ->get()
                ->groupBy('form_id');

            $transformedWorkflows = $workflows->map(function ($formGroup, $formId) {
                return [
                    'form_id' => $formId,
                    'form_name' => optional($formGroup->first()->form)->name,
                    'workflow_id' => $formGroup->first()->workflow_id,
                    'approvers' => $formGroup->groupBy('approver_id')->map(function ($approverGroup, $approverId) {
                        $firstItem = $approverGroup->first();
                        // dd($firstItem);
                        return [
                            'approver_id' => $approverId,
                            'approver_name' => optional($firstItem->approver)->name,
                            'sequence_no' => $firstItem->sequence_no,
                            'users' => $approverGroup->map(callback: function ($workflow) {
                                return [
                                    'id' => optional($workflow->user)->id,
                                    'name' => optional($workflow->user)->name,
                                    'status' => $workflow->status,
                                ];
                            }),
                        ];
                    })->values()
                ];
            })->values();

            return Helper::sendResponse($transformedWorkflows, 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }
}
