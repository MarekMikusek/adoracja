<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property int $hour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $inactive
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|CurrentDuty newModelQuery()
 * @method static Builder<static>|CurrentDuty newQuery()
 * @method static Builder<static>|CurrentDuty query()
 * @method static Builder<static>|CurrentDuty whereCreatedAt($value)
 * @method static Builder<static>|CurrentDuty whereDate($value)
 * @method static Builder<static>|CurrentDuty whereHour($value)
 * @method static Builder<static>|CurrentDuty whereId($value)
 * @method static Builder<static>|CurrentDuty whereInactive($value)
 * @method static Builder<static>|CurrentDuty whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CurrentDuty extends Model
{
    protected $fillable = [
        'date',
        'hour',
    ];

    protected $casts = [
        'date' => 'datetime',
        'hour' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'current_duties_users', 'current_duty_id');
    }
}
