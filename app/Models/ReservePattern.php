<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReservePattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReservePattern newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReservePattern query()
 * @mixin \Eloquent
 */
class ReservePattern extends Model
{
    protected $table = 'reserve_patterns';

    public $patternKey = 'reserve';

    protected $fillable = [
        'user_id',
        'hour',
        'day',
        'start_date',
        'repeat_interval',
        'suspension_begin',
        'suspension_end',
        'repeat_pattern',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
