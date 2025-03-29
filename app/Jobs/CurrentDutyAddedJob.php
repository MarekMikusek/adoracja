<?php

namespace App\Jobs;

use App\Mail\CurrentDutyAddedMail;
use App\Models\CurrentDuty;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class CurrentDutyAddedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public CurrentDuty $currentDuty, public string $duty_type)
    {}

    public function handle(): void
    {
        if($this->user->notification_preference == 'email'){
            Mail::to($this->user->email)->send(new CurrentDutyAddedMail($this->user, $this->currentDuty, $this->duty_type));
        } else {

        }

    }
}
