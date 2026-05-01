<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DutyNotification extends Mailable implements ShouldQueue
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
