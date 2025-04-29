<?php

namespace App\Jobs;

use App\Mail\ParallelApproverEmail;
use App\Models\Form;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendApprovalEmailToParallelApproverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $parallelApprovers;
    protected $form;
    /**
     * Create a new job instance.
     */
    public function __construct(array $parallelApprovers)
    {
        $this->parallelApprovers = $parallelApprovers;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            foreach ($this->parallelApprovers as $approverGroup) {
                $formId = $approverGroup['form_id'] ?? null;

                if ($formId && $this->form === null) {
                    $this->form = Form::find($formId);
                }

                $user = User::find($approverGroup['user_id']);
                $parallelUserId = $approverGroup['parallel_user_id'];

                $parallelUser = User::find($parallelUserId);

                if ($parallelUser) {
                    $this->sendEmail($approverGroup, $user, $parallelUser);
                } else {
                    Log::warning('Parallel user not found', ['user_id' => $parallelUserId]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function sendEmail($approver, $user, $parallelUser) {

        $data = [
            'form_name' => $this->form ? $this->form->name : null,
            'slug' => $this->form->slug,
            'employee_no' => $user->employee_no,
            'email' => $user->email,
            'name' => $user->name,
            'key' => $approver['key'],
            'status' => $approver['status'],
            'workflow_id' => $approver['workflow_id'],
        ];

        Mail::to($parallelUser->email)->send(new ParallelApproverEmail($data));
        if (empty($parallelUser->email)) {
            Log::error('Attempting to send an email to a user with no email address.', [
                'approver' => $approver,
                'user' => $user,
                'parallelUser' => $parallelUser
            ]);
        }
    }
}
