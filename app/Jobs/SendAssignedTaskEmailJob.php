<?php

namespace App\Jobs;

use App\Mail\ApprovalEmail;
use App\Mail\AssignedTaskEmail;
use App\Models\Form;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendAssignedTaskEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $managerIds;
    protected $memberIds;
    protected $task;

    /**
     * Create a new job instance.
     */
    public function __construct(array $managerIds, array $memberIds, array $task)
    {
        $this->managerIds = $managerIds;
        $this->memberIds = $memberIds;
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userIds = array_unique(array_merge($this->managerIds, $this->memberIds,  [$this->task['task_assigned_by']]));

        $users = User::whereIn('id', $userIds)->get(['id', 'email', 'name']);
        $initiator = $users->firstWhere('id', $this->task['task_assigned_by']);
        $initiatorName = $initiator ? $initiator->name : 'Unknown';

        $form = Form::where('identity', $this->task['assignable_type'])->first(['id', 'name', 'identity', 'slug']);
        foreach ($users as $user) {
            if ($form) {
                $data = [
                    'form_name' => $form ? $form->name : 'Support Desk Form',
                    'slug' => $form ? $form->slug : 'support-desk-form',
                    'key' => $this->task['assignable_id'],
                    'email' => $user->email,
                    'name' => $user->name,
                    'initiator_name' => $initiatorName,
                ];
            } else {
                $data = [
                    'form_name' => 'Support Desk Form',
                    'slug' => 'support-desk-form',
                    'key' => $this->task['assignable_id'],
                    'email' => $user->email,
                    'name' => $user->name,
                    'initiator_name' => $initiatorName,
                ];
            }
            Mail::to($user->email)->send(new AssignedTaskEmail($data));
        }
    }


    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception)
    {
        logger()->error($exception->getMessage());
    }
}
