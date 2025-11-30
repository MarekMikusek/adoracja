<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CurrentDutyUser extends Model
{
    protected $table = 'current_duties_users';

    protected $fillable = [
        'user_id',
        'duty_type',
        'current_duty_id',
    ];

    public function scopeFindUserDuties(Builder $query, User $user)
    {
        $query->where('user_id', $user->id);
    }
}
