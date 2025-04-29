<?php

namespace App\Traits;

use App\Http\Helpers\Helper;
use App\Models\ApprovalStatus;
use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use App\Services\GlobalFormService;
use Auth;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait CommonControllerShowTrait
{
    // public function __construct()
    // {
    //    $this->middleware('transaction.limit.check')->only(['store']);
    // }
    protected function showCommonRelationships()
    {
        return array_merge([
            'approvalStatuses' => function ($query): void {
                $query->select('user_id', 'form_id', 'key', 'approver_id', 'approval_required', 'sequence_no', 'status', 'status_at', 'responded_by', 'condition_id', 'editable')
                    ->with([
                        'user:id,name,email,employee_no',
                        'approver:id,name',
                        'user.parallelApprovers:id,name',
                        'respondedBy:id,name',
                    ]);
            }
        ], $this->getCommonRelationships());
    }
    protected function getCommonRelationships(): array
    {
        return [
            'department:id,name',
            'location:id,name',
            'designation:id,name',
            'section:id,name,department_id',
            'approvalStatuses',
            'deployments',
        ];
    }
}
