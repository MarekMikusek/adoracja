<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
