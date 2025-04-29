<?php

namespace App\Jobs;

use App\Mail\ApprovalEmail;
use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendApprovalEmailJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $approvalStatuses;

    /**
     * Create a new job instance.
     */
    public function __construct(array $approvalStatuses) {
        $this->approvalStatuses = $approvalStatuses;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $approvalStatuses = $this->approvalStatuses;

        $formId = $this->getFormId($approvalStatuses);
        if (!$formId) {
            Log::error('form_id is missing in SendApprovalEmailJob', ['approvalStatuses' => $approvalStatuses]);
            return;
        }

        $form = Form::find($formId);
        if (!$form) {
            Log::error('Form not found', ['form_id' => $formId]);
            return;
        }

        $result = $form->identity::withoutGlobalScope(FormDataAccessScope::class)->find($approvalStatuses[0]['key']);
        $processingStatuses = array_filter($approvalStatuses, function($status) {
            return $status['status'] === 'Processing';
        });
        foreach ($processingStatuses as $approvalStatus) {
            $user = User::find($approvalStatus['user_id']);
            if ($user) {
                $data = [
                    'form_name' => $form->name,
                    'slug' => $form->slug,
                    'request_title' => $result->request_title,
                    'employee_no' => $user->employee_no,
                    'email' => $user->email,
                    'name' => $user->name,
                    'key' => $approvalStatus['key'],
                    'status' => $approvalStatus['status'],
                    'workflow_id' => $approvalStatus['workflow_id'],
                ];

                Mail::to($user->email)->send(new ApprovalEmail($data));
            }
        }
    }

    /**
     * Extract form_id from approval statuses.
     */
    protected function getFormId(array $approvalStatuses): ?int {
        if (isset($approvalStatuses[0]['form_id'])) {
            return $approvalStatuses[0]['form_id'];
        } elseif (isset($approvalStatuses['form_id'])) {
            return $approvalStatuses['form_id'];
        }
        return null;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception) {
        logger()->error($exception->getMessage());
    }
}
