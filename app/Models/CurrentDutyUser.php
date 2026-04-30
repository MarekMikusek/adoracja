<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $current_duty_id
 * @property int $user_id
 * @property string $duty_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $changed_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\CurrentDuty|null $currentDuty
 * @method static Builder<static>|CurrentDutyUser findUserDuties(\App\Models\User $user)
 * @method static Builder<static>|CurrentDutyUser newModelQuery()
 * @method static Builder<static>|CurrentDutyUser newQuery()
 * @method static Builder<static>|CurrentDutyUser onlyTrashed()
 * @method static Builder<static>|CurrentDutyUser query()
 * @method static Builder<static>|CurrentDutyUser whereChangedBy($value)
 * @method static Builder<static>|CurrentDutyUser whereCreatedAt($value)
 * @method static Builder<static>|CurrentDutyUser whereCurrentDutyId($value)
 * @method static Builder<static>|CurrentDutyUser whereDeletedAt($value)
 * @method static Builder<static>|CurrentDutyUser whereDutyType($value)
 * @method static Builder<static>|CurrentDutyUser whereId($value)
 * @method static Builder<static>|CurrentDutyUser whereUpdatedAt($value)
 * @method static Builder<static>|CurrentDutyUser whereUserId($value)
 * @method static Builder<static>|CurrentDutyUser withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|CurrentDutyUser withoutTrashed()
 * @mixin \Eloquent
 */
class CurrentDutyUser extends Model
{
    use SoftDeletes;
    protected $table = 'current_duties_users';

    protected $fillable = [
        'user_id',
        'duty_type',
        'current_duty_id',
        'from_pattern',
        'changed_by'
    ];

    public function scopeFindUserDuties(Builder $query, User $user)
    {
        $query->where('user_id', $user->id);
    }

    public function currentDuty(): BelongsTo
    {
        return $this->belongsTo(CurrentDuty::class);
    }
}
