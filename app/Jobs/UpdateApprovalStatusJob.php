<?php

namespace App\Jobs;

use App\Enums\FormEnum;
use App\Models\ApprovalStatus;
use App\Models\Scopes\FormDataAccessScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateApprovalStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $formId;
    protected $dataId;
    protected $approverId;
    protected $sequenceNo;
    protected $currentUser;
    protected $workflowId;

    public function __construct($formId, $dataId, $approverId, $sequenceNo, $currentUser, $workflowId)
    {
        $this->formId = $formId;
        $this->dataId = $dataId;
        $this->approverId = $approverId;
        $this->sequenceNo = $sequenceNo;
        $this->currentUser = $currentUser;
        $this->workflowId = $workflowId;
    }

    public function handle()
    {

        $model = FormEnum::getModelById($this->formId);

        $count = ApprovalStatus::where('form_id', $this->formId)
            ->where('key', $this->dataId)->count();
        if ($count == 0) {
            $modelInstance = $model::withoutGlobalScope(FormDataAccessScope::class)->find($this->dataId);

            if ($modelInstance) {
                return $modelInstance->update(['status' => 'Approved']);
            }
        }

        $unapprovedCount = ApprovalStatus::where('form_id', $this->formId)
            ->where('key', $this->dataId)
            ->where('status', '!=', 'Approved')
            ->count();

        if ($unapprovedCount == 0) {
            $modelInstance = $model::withoutGlobalScope(FormDataAccessScope::class)->find($this->dataId);

            if ($modelInstance) {
                $modelInstance->update(['status' => 'Approved']);
                Log::info('Model updated: ' . $modelInstance);
            } else {
                Log::error('Model not found for update. formId: ' . $this->formId . ', dataId: ' . $this->dataId);
            }
        }
    }
}
