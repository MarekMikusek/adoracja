<?php

namespace App\Jobs;

use App\Mail\UserMessageMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class UserMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
        public User $user
    ){}

    public function handle(): void
    {
        \Log::info(__METHOD__);
        Mail::to($this->user->email)->send(new UserMessageMail($this->message, $this->user));
    }
}
