<?php
namespace App\Jobs;

use App\Mail\ConfirmAccountMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class AccountRegisteredJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user)
    {}

    public function handle(): void
    {
        \Log::info(__METHOD__);
        if ($this->user->notification_preference == 'email') {
            Mail::to($this->user->email)->send(new ConfirmAccountMail($this->user));
        }
    }
}
