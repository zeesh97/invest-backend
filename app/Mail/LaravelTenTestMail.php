<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class LaravelTenTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('workflow@signaps.com', 'Ahsan Najam'),
            subject: 'Laravel Ten Queue Test Mail'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: ['data' => $this->data]
        );
    }
}
