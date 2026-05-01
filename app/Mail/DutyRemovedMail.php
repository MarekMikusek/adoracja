<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\DutyPattern;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DutyRemovedMail extends Mailable //implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    private User $user;
    private DutyPattern $dutyPattern;

    public function __construct(User $user, DutyPattern $dutyPattern)
    {
        $this->user = $user;
        $this->dutyPattern = $dutyPattern;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'adoracja@adoracja.chjz.pl',
            subject: 'Adoracja ChJZ - usunięto posługę',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if($this->dutyPattern->repeat_interval == 1){
            $intvalStr = 'posługa co tydzień';
        } else {
            $startDate = Carbon::parse($this->dutyPattern->start_date)->format('Y-m-d');
            $intvalStr = "posługa co {$this->dutyPattern->repeat_interval} tygodnie, pierwsza {$startDate}";
        }
        return new Content(
            view: 'emails.duty-pattern-removed',
            with: [
                'userFirstName' => $this->user->first_name,
                'dutyType' => $this->dutyPattern->duty_type,
                'hour' => $this->dutyPattern->hour,
                'day' => strtolower($this->dutyPattern->day)
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
