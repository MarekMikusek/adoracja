<?php

namespace App\Models;

use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DutyPattern extends Model
{
    protected $table = 'duty_patterns';
    protected $fillable = [
        'user_id',
        'day',
        'hour',
        'start_date',
        'duty_type',
        'repeat_interval',
        'repeat_pattern',
    ];

    public $patternKey = 'duty';

    protected $casts = [
        'is_admin_duty' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public static function getUsersForTimeFrame(Carbon $weekStartDate, string $weekDay, int $hour, Collection $users)
    {
        $mappedUsers = $users->keyBy('id');
        $allDuties = self::where(['day' => $weekDay])->where('hour', $hour)->get();

        foreach ($allDuties as &$duty) {
            if (!$duty->isDutyInWeek($weekStartDate) || $mappedUsers[$duty->user_id]->isSuspended($weekStartDate)) {
                unset($duty);
            }
        }

        // Return a collection of arrays with user_id and duty_type
        return $allDuties->map(function ($duty) {
            return [
                'user_id' => $duty->user_id,
                'duty_type' => $duty->duty_type,
            ];
        });
    }

    public function isDutyInWeek(Carbon $startDate): bool {
        // Get the start and target week numbers
        $startWeek = $startDate->copy()->startOfWeek()->weekOfYear;
        $targetDate = $startDate->addDays(Helper::dayNumber($this->day));
        $targetWeek = $targetDate->copy()->startOfWeek()->weekOfYear;

        // Calculate the difference in weeks
        $weeksDifference = $targetWeek - $startWeek;

        // Check if the duty repeats in this week
        return $weeksDifference >= 0 && $weeksDifference % $this->repeat_interval === 0;
    }
}
