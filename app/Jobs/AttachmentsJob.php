<?php

namespace App\Jobs;

use App\Models\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AttachmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $attachments;

    public function __construct($attachments)
    {
        $this->attachments = $attachments;
    }

    public function handle(): void
    {
        foreach ($this->attachments as $attachment) {
            Attachment::create($attachment);
        }
    }
}
