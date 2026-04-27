<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
