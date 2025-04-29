<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    // protected $message;

    // public function __construct($message)
    // {
    //     $this->message = $message;
    // }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Approval email for Test',
        );
    }

    public function content(): Content
    {
        $message = "Hi,\n\n";
    $message .= "Your Test has been approved.\n";
    $message .= "Status: Good\n\n";
    $message .= "Thanks,\n";
    $message .= "The Team";

    return new Content(
        text: $message
    );
    }
}
