<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public $emailContent, public User $user)
    {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Wiadomość od administratora Adoracji W ChJZ',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_message',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
