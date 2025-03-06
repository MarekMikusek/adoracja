<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'notification_preference',
        'is_admin',
        'is_confirmed',
        'confirmation_token',
    ];

    protected $hidden = [
        'password',
        'confirmation_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_confirmed' => 'boolean',
    ];

    public function dutyPatterns()
    {
        return $this->hasOne(DutyPattern::class);
    }

    public function reservePatterns()
    {
        return $this->hasOne(ReservePattern::class);
    }

    public function currentDuties(): BelongsToMany
    {
        return $this->belongsToMany(CurrentDuty::class, 'current_duties_users', 'user_id');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if (!$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }
        });
    }
}
