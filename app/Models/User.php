<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $phone_number
 * @property string $password
 * @property int $ways_of_contacts_id
 * @property string|null $suspend_from
 * @property string|null $suspend_to
 * @property bool $is_admin
 * @property string|null $remember_token
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $google_token
 * @property int $added_by
 * @property int|null $rodo_clause
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdminDutyPattern> $AdminDutyPatterns
 * @property-read int|null $admin_duty_patterns_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CurrentDuty> $currentDuties
 * @property-read int|null $current_duties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DutyPattern> $dutyPatterns
 * @property-read int|null $duty_patterns_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Testimony> $testimonies
 * @property-read int|null $testimonies_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User hasPattern()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRodoClause($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSuspendFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSuspendTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWaysOfContactsId($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable//implements MustVerifyEmail

{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'suspend_from',
        'suspend_to',
        'confirmation_token',
        'is_admin',
        'ways_of_contacts_id',
        'google_token',
        'color',
        'password',
        'added_by',
        'rodo_clause',
    ];

    protected $hidden = [
        'password',
        'confirmation_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function isAdmin()
    {
        return $this->is_admin == 1;
    }

    public function AdminDutyPatterns(): HasMany
    {
        return $this->hasMany(AdminDutyPattern::class, 'admin_id');
    }

    public function dutyPatterns(): HasMany
    {
        return $this->hasMany(DutyPattern::class);
    }

    public function currentDuties(): BelongsToMany
    {
        return $this->belongsToMany(CurrentDuty::class, 'current_duties_users', 'user_id');
    }

    public function isSuspended(Carbon $date): bool
    {
        $suspendFrom = Carbon::parse($this->suspend_from);
        $suspendTo   = Carbon::parse($this->suspend_to);
        if ($date->between($suspendFrom, $suspendTo) || ($date >= $suspendFrom && ! $suspendTo)) {
            return true;
        }

        return false;
    }

    public static function admins()
    {
        return DB::select("select id, concat(first_name, ' ', last_name) as name, color, phone_number from users where is_admin = true");
    }

    public function testimonies()
    {
        return $this->hasMany(Testimony::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasPattern($query)
    {
        return $query->has('dutyPatterns');
    }

    public function hasRealEmail()
    {
        if (empty($this->email) || str_contains($this->email, 'fikcyjn') || str_contains($this->email, 'adoracja')) {
            return false;
        }

        return true;
    }
}
