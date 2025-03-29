<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestEmail extends Mailable
{
    public function build()
    {
        return $this->subject('Test Email')
                    ->view('emails.test'); // Create this Blade file
    }
}