<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'suspend_from',
        'suspend_to',
        'is_confirmed',
        'is_admin',
        'notification_preference',
        'google_token',
        'color',
        'password'
    ];

    protected $hidden = [
        'password',
        'confirmation_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_confirmed' => 'boolean',
    ];

    public function dutyPatterns(): HasMany
    {
        return $this->hasMany(AdminDutyPattern::class, 'admin_id');
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

    public function isSuspended(Carbon $weekStartDate): bool
    {
        $currentDate = $weekStartDate->addDays(Helper::dayNumber($this->day));

        $suspendFrom = Carbon::parse($this->suspend_from);
        $suspendTo = Carbon::parse($this->suspend_to);
        if($currentDate->between($suspendFrom, $suspendTo) || ($currentDate >= $suspendFrom && !$suspendTo)) {
            return true;
        }

        return false;
    }

    public static function admins()
    {
        return DB::select("select id, concat(first_name, ' ', last_name) as name, color from users where is_admin = true");
    }
}
