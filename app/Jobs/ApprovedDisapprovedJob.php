<?php

namespace App\Jobs;

use App\Mail\ApprovedDisapprovedEmail;
use App\Models\EmailLog;
use App\Models\Form;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ApprovedDisapprovedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $modelData;
    protected $modelClass;
    protected $emailLog;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $modelData, string $modelClass)
    {
        $this->modelData = $modelData;
        $this->modelClass = $modelClass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $form = Form::where('identity', $this->modelClass)->first();
        $user_id = $this->modelData['created_by'];
        $user = User::find($user_id)->select(['id', 'name', 'email', 'employee_no'])->first();

        $data = [
            'form_name' => $form->name,
            'slug' => $form->slug,
            'request_title' => $this->modelData['request_title'],
            'employee_no' => $user->employee_no,
            'email' => $user->email,
            'name' => $user->name,
            'key' => $this->modelData['id'],
            'status' => $this->modelData['status'],
        ];

        // Create an email log entry
        $this->emailLog = EmailLog::create([
            'recipient' => $user->email,
            'subject' => 'Status email for ' . $data['form_name'],
            'body' => 'Preview: ' . substr($data['request_title'], 0, 50),
            'status' => 'queued',
        ]);

        try {
            Mail::to($user->email)->send(new ApprovedDisapprovedEmail($data));

            $this->emailLog->update(['status' => 'delivered']);
        } catch (\Exception $e) {
            // Update email log status to failed
            $this->emailLog->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Exception $exception): void
    {
        // Update email log status to failed with reason
        if ($this->emailLog) {
            $this->emailLog->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
        }
    }
}

