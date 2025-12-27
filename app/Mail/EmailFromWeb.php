<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class EmailFromWeb extends Mailable
{
   public string $messageText;

    /**
     * Create a new message instance.
     */
    public function __construct(string $messageText)
    {
        $this->messageText = $messageText;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Wiadomość ze strony adoracja.chjz.pl')
                    ->view('emails.from_web')
                    ->with([
                        'messageText' => $this->messageText,
                    ]);
    }
}
