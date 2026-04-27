<?php
namespace App\Models;

use App\Services\DateHelper;
use Illuminate\Database\Eloquent\Model;
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
        return self::leftJoin('users as u', 'admin_duty_patterns.admin_id', 'u.id')
            ->selectRaw("hour, day, admin_id")
            ->orderBy('admin_duty_patterns.id')
            ->get()
            ->groupBy('day')
            ->mapWithKeys(function ($items, $day) {
                return [$day => $items->keyBy('hour')->toArray()];
            });
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getAdmin($duty)
    {
        $admin = DB::table('admin_duty_patterns')
            ->join('users as u', 'admin_id', 'u.id')
            ->where('day', DateHelper::dayOfWeek($duty->date))
            ->where('hour', $duty->hour)
            ->select('first_name', 'last_name')
            ->first();

        if (! $admin) {
            return '';
        }
        
        return $admin->first_name . ' ' . $admin->last_name;
    }
}
