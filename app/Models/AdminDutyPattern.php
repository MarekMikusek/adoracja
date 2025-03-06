<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDutyPattern extends Model
{
    protected $table = 'admin_duty_patterns';

    public $patternKey = 'admin';

    protected $fillable = [
        'user_id',
        'time_frame_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
