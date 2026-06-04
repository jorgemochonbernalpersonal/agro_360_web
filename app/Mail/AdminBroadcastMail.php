<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $broadcastSubject;

    public string $broadcastMessage;

    public function __construct(string $subject, string $message)
    {
        $this->broadcastSubject = $subject;
        $this->broadcastMessage = $message;
    }

    public function build(): static
    {
        return $this->subject($this->broadcastSubject)
            ->view('emails.admin-broadcast');
    }
}
