<?php

namespace App\Jobs;

use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\User;
use App\Mail\MentionedInCommentEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Mail;

class MentionedInCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $mentionedUserIds;
    protected $modelClass;
    protected $key;
    protected $formName;
    protected $slug;

    public function __construct($mentionedUserIds, $modelClass, $key, $formName, $slug)
    {
        $this->mentionedUserIds = $mentionedUserIds;
        $this->modelClass = $modelClass;
        $this->key = $key;
        $this->formName = $formName;
        $this->slug = $slug;
    }

    public function handle(): void
    {
        $record = $this->modelClass::withoutGlobalScope(FormDataAccessScope::class)->find($this->key);
        $users = User::whereIn('id', $this->mentionedUserIds)
                     ->select(['id', 'name', 'email'])
                     ->get();

        foreach ($users as $user) {
            $data = [
                'form_name' => $this->formName,
                'slug' => $this->slug,
                'request_title' => $record->request_title,
                'employee_no' => $user->employee_no,
                'email' => $user->email,
                'name' => $user->name,
                'key' => $this->key,
                'status' => $record->status,
            ];
            Log::info($data);
            Mail::to($user->email)->send(new MentionedInCommentEmail($data));
        }
    }
}
