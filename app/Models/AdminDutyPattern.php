<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\DateHelper;
use Illuminate\Support\Facades\DB;

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

    public static function adminDutyPatterns()
    {
        return self::join('users as u', 'admin_duty_patterns.admin_id', 'u.id')
        ->selectRaw("hour, day, admin_id")
        ->orderBy('admin_duty_patterns.id')
        ->get()
        ->groupBy('day')
        ->mapWithKeys(function($items, $day){
            return [$day => $items->keyBy('hour')->toArray()];
        });
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getAdmin($duty)
    {
        return DB::table('admin_duty_patterns')
        ->where('day', DateHelper::dayOfWeek($duty->date))
        ->where('hour', $duty->hour)
        ->select('admin_id')
        ->first()
        ->admin_id;
    }
}
