<?php

namespace App\Jobs;

use App\Mail\SubscriberEmail;
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

class SendSubscriberEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 10;
    protected $subscribers;

    /**
     * Create a new job instance.
     */
    public function __construct(array $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscribers = $this->subscribers;

        $form = Form::find($subscribers[0]['form_id']);
        $result = $form->identity::withoutGlobalScope(FormDataAccessScope::class)->find($subscribers[0]['key']);
        foreach ($this->subscribers as $subscriber) {
            $data = [];
            $data['form_name'] = $form->name;
            $data['slug'] = $form->slug;
            $data['request_title'] = $result->request_title;
            $data['employee_no'] = $subscriber['employee_no'];
            $data['email'] = $subscriber['email'];
            $data['name'] = $subscriber['name'];
            $data['key'] = $subscriber['key'];

            Mail::to($subscriber['email'])->send(new SubscriberEmail($data));

        }

    }

    public function failed(\Exception $exception)
    {

        logger()->error($exception->getMessage());
    }

}
