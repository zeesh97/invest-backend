<?php

namespace App\Jobs;

use App\Mail\ApprovalEmail;
use App\Mail\SendQaRequestEmail;
use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\User;
use Crypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendQaRequestEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $key;
    protected $form_id;
    protected $user_ids;

    /**
     * Create a new job instance.
     */
    public function __construct(int $key, int $form_id, array $user_ids)
    {
        $this->key = $key;
        $this->form_id = $form_id;
        $this->user_ids = $user_ids;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $form = Form::find($this->form_id);
        if (!$form) {
            Log::error('Form not found', ['form_id' => $this->form_id]);
            return;
        }
        $users = User::whereIn('id', $this->user_ids)->select(['id', 'name', 'email', 'employee_no'])->get();

        $formName = $this->getFormName($form->identity);
        $formData = $form->identity::withoutGlobalScope(FormDataAccessScope::class)->findOrFail($this->key);
        $params = self::generateEncryptedDetail($this->form_id, $this->key);
        foreach ($users as $user) {
            $data = [
                'form_name' => $formName,
                'slug' => $form->slug,
                'request_title' => $formData->request_title,
                'name' => $user->name,
                'employee_no' => $user->employee_no,
                'email' => $user->email,
                // 'form_id' => $this->form_id,
                // 'key' => $this->key,
                'params' => $params,
                'user_ids' => $this->user_ids,
            ];

            Mail::to($user->email)->send(new SendQaRequestEmail($data));
        }
    }

    public function getFormName(mixed $model): string
    {
        $parts = explode('\\', $model);
        $lastPart = end($parts);

        if (preg_match('/[a-z]/', $lastPart)) {
            // If there are lowercase letters, add spaces
            $lastPart = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $lastPart);
        }
        return $lastPart;
    }
    public function generateEncryptedDetail($form_id, $key)
    {
        $data = json_encode(['form_id' => $form_id, 'key' => $key]);
        return Crypt::encryptString($data);
    }
}
