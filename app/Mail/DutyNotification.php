<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DutyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function build()
    {
        return $this->view('emails.duty-notification')
                    ->subject('Powiadomienie o dyżurze');
    }
} 