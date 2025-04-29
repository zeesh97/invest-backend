<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Models\ApprovalStatus;
use App\Models\Form;
use App\Models\ParallelApprover;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\Workflow;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // public function __construct()
    // {
    //     $this->getTotalWorkflowsCount();
    //     $this->getPendingForApprovalCount();
    //     $this->getapprovalStatuses();
    // }
    protected $data = [];
    public static function applyCommonCondition(Builder $query, $relation, $column)
    {
        if (!Auth::user()->hasRole('admin')) {
            // Apply your condition here
            $query->whereHas($relation, function ($innerQuery) use ($column) {
                $innerQuery->where($column, auth()->user()->id);
            });
        }
    }

    // public function getTotalWorkflowsCount(){

    //     Workflow::with('workflowInitiatorField')
    //     ->whereHas('workflowInitiatorField', function($query){
    //         $query->where('initiator_id', auth()->user()->id);
    //     })
    //     ->count();
    // }
    public function getTotalWorkflowsCount()
    {
        $query = Workflow::with('workflowInitiatorField');
        self::applyCommonCondition($query, 'workflowInitiatorField', 'initiator_id');
        $data['total_workflows_count'] = $query->count();
        return $data;
    }

    public function getPendingForApprovalCount()
    {
        try {
            $data['processing_count'] = $this->getApprovalStatusCount(['Processing']);
            $data['pending_count'] = $this->getApprovalStatusCount(['Pending']);
            $data['approved_count'] = $this->getApprovalStatusCount(['Approved']);
            $data['disapproved_count'] = $this->getApprovalStatusCount(['Disapproved']);
            $data['return_count'] = $this->getApprovalStatusCount(['Return']);

            return Helper::sendResponse($data, 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendResponse($e->getMessage(), 'Request failed', 433);
        }
    }

    public function getReturnCount()
    {
        try {
            $data['return_count'] = $this->getApprovalStatusCount(['Return']);

            return Helper::sendResponse($data, 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendResponse($e->getMessage(), 'Request failed', 433);
        }
    }

    public function getapprovalStatuses(int $id)
    {
        try {
            $form = Form::find($id);
            if ($form) {
                $model = $form->identity;

                $query = $model::with('workflow.approvalStatuses');
                self::applyCommonCondition($query, 'workflow.approvalStatuses', 'user_id');
                $data['total_approval_statuses'] = $query->get();

                return Helper::sendResponse($data, 'Success', 200);
            } else {
                throw new \Exception("Form not found");
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), 'Request failed', 433);
        }
    }

    protected function getApprovalStatusCount(array $statuses)
    {
        return ApprovalStatus::whereIn('status', $statuses)
            ->where('user_id', auth()->user()->id)->count();
    }

    public function getApprovalsByFormId(Request $request)
    {
        if ($request->status && $request->status != null) {
            $status = ucfirst($request->status);
        }

        if ($request->tab && $request->tab != null) {
            $tab = ucfirst($request->tab);
        }

        $validated = $request->validate([
            'tab' => ['required', 'in:Initiated,Approval,Parallel'],
            'form_id' => ['required', 'exists:forms,id'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $form = Form::findOrFail($validated['form_id']);
        if ($tab == "Approval") {
            $request->validate([
                'status' => ['required', 'in:Processing,Approved,Disapproved,Return']
            ]);

            $perPage = $request->has('per_page') ? $request->per_page : 10;
            $formIdentity = $form->identity::withoutGlobalScope(FormDataAccessScope::class);
            if ($status === 'Processing') {
                $approval_statuses = ApprovalStatus::select('key')
                    ->where('form_id', $validated['form_id'])
                    ->where('status', $status)
                    ->where('user_id', auth()->user()->id)
                    ->orderByDesc('key')
                    ->get();
                $identityRecords = $formIdentity->whereIn('id',  $approval_statuses->pluck('key'))
                    ->get(['id', 'sequence_no', 'request_title', 'status']);
                $identityMapping = $identityRecords->keyBy('id');


                $mergedData = $approval_statuses->map(function ($status) use ($identityMapping) {
                    $status->identityRecord = $identityMapping->get($status->key);
                    return $status;
                });
            }
            if ($status === 'Approved') {
                $approval_statuses = ApprovalStatus::select('key')
                    ->where('form_id', $validated['form_id'])
                    ->where('status', $status)
                    ->where('user_id', auth()->user()->id)
                    ->orderByDesc('key')
                    ->orderByRaw('reason IS NULL')
                    ->get();

                $identityRecords = $formIdentity->whereIn('id',  $approval_statuses->pluck('key'))
                    ->get(['id', 'sequence_no', 'request_title', 'status']);
                // dd($mergedData);
                $identityMapping = $identityRecords->keyBy('id');


                $mergedData = $approval_statuses->map(function ($status) use ($identityMapping) {
                    $status->identityRecord = $identityMapping->get($status->key);
                    return $status;
                });
            }

            if ($status === 'Disapproved') {
                $approvalKeys = ApprovalStatus::select('key')
                    ->where('form_id', $validated['form_id'])
                    ->where('status', $status)
                    ->where('user_id', auth()->user()->id)
                    ->pluck('key'); // Use pluck to get an array of 'key' values

                // Rejoin with the same table using the keys obtained in the previous step
                $approval_statuses = ApprovalStatus::select(['key', 'reason'])
                    ->where('form_id', $validated['form_id'])
                    ->whereIn('key', $approvalKeys) // Rejoining using keys from the first query
                    ->where('status', $status)
                    ->where('status_at', '<>', null)
                    ->orderByDesc('key')
                    ->orderByRaw('reason IS NULL')
                    ->get();
                $identityRecords = $formIdentity->whereIn('id',  $approval_statuses->pluck('key'))
                    ->get(['id', 'sequence_no', 'request_title', 'status']);
                $identityMapping = $identityRecords->keyBy('id');


                $mergedData = $approval_statuses->map(function ($status) use ($identityMapping) {
                    $status->identityRecord = $identityMapping->get($status->key);
                    return $status;
                });
            }
            if ($status === 'Return') {
                $approvalKeys = ApprovalStatus::select('key')
                    ->where('form_id', $validated['form_id'])
                    ->where('status', $status)
                    ->where('user_id', auth()->user()->id)
                    ->pluck('key'); // Use pluck to get an array of 'key' values

                // Rejoin with the same table using the keys obtained in the previous step
                $approval_statuses = ApprovalStatus::select(['key', 'reason'])
                    ->where('form_id', $validated['form_id'])
                    ->whereIn('key', $approvalKeys) // Rejoining using keys from the first query
                    ->where('status', $status)
                    ->where('status_at', '<>', null)
                    ->orderByDesc('key')
                    ->orderByRaw('reason IS NULL')
                    ->get();
                $identityRecords = $formIdentity->whereIn('id',  $approval_statuses->pluck('key'))
                    ->get(['id', 'sequence_no', 'request_title', 'status']);
                $identityMapping = $identityRecords->keyBy('id');


                $mergedData = $approval_statuses->map(function ($status) use ($identityMapping) {
                    $status->identityRecord = $identityMapping->get($status->key);
                    return $status;
                });
            }

            $paginatedData = new LengthAwarePaginator(
                $mergedData,
                $mergedData->count(),
                $perPage,
                $request->page

            );

            return Helper::sendResponse($paginatedData, 'Success', 200);
        }

        if ($tab == "Initiated") {
            $request->validate([
                'status' => ['required', 'in:Processing,Approved,Disapproved,Return']
            ]);
            $status = $request->status;
            if ($request->status == 'Processing') {
                $status = 'Pending';
            }

            $mergedData = $form->identity::withoutGlobalScope(FormDataAccessScope::class)->where('created_by', Auth::user()->id)
                ->where('status', $status)->latest()
                ->get();

            $reasons = ApprovalStatus::where('form_id', $validated['form_id'])
                ->whereIn('key', $mergedData->pluck('id'))->orderByDesc('reason')
                ->get();
            // dd($reasons);
            $formattedData = $mergedData->map(function ($identityRecord) use ($reasons, $validated) {
                return [

                    'form_id' => $validated['form_id'],
                    'key' => $identityRecord->id,
                    'reason' => $reasons->where('key', $identityRecord->id)->first()?->reason,
                    'identityRecord' => $identityRecord,
                ];
            });
            $perPage = $request->has('per_page') ? $request->per_page : 10;

            // Create paginator and return response
            $paginatedData = new LengthAwarePaginator(
                $formattedData,
                $mergedData->count(),
                $perPage,
                $request->page
            );

            return Helper::sendResponse($paginatedData, 'Success', 200);
        }
        if ($tab == "Parallel") {

            $request->validate([
                'status' => ['required', 'in:Processing,Approved,Disapproved,Pending,Return']
            ]);
            $assignedUserIds = ParallelApprover::where('parallel_user_id', auth()->user()->id)
                ->pluck('user_id')->toArray();
            $perPage = $request->has('per_page') ? $request->per_page : 10;

            if ($status === 'Approved') {
                $approval_statuses = ApprovalStatus::where('form_id', $validated['form_id'])
                    ->whereIn('user_id', $assignedUserIds)
                    ->where('status', $status)
                    ->orderBy('status_at')->orderByDesc('reason')->get();
            } else {
                $approval_statuses = ApprovalStatus::where('form_id', $validated['form_id'])
                    ->whereIn('user_id', $assignedUserIds)
                    ->where('status', $status)
                    ->get();
            }

            $identityRecords = $form->identity::withoutGlobalScope(FormDataAccessScope::class)->with('user:id,name,email')
                ->whereIn('id', $approval_statuses->pluck('key'))->latest()->get();

            $identityMapping = $identityRecords->keyBy('id');

            $mergedData = $approval_statuses->map(function ($status) use ($identityMapping) {
                $status->identityRecord = $identityMapping->get($status->key);
                return $status;
            });

            $mergedData = $mergedData->sortByDesc('key')->values();

            $paginatedData = new LengthAwarePaginator(
                $mergedData,
                $mergedData->count(),
                $perPage,
                $request->page
            );

            return Helper::sendResponse($paginatedData, 'Success', 200);
        }
    }
}
