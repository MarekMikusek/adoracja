<?php

namespace App\Models;

use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        'repeat_interval',
        'suspend_from',
        'suspend_to',
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

    public static function getUsersForTimeFrame(Carbon $weekStartDate, string $weekDay, int $hour): Collection
    {
        $allDuties = self::where(['day'=> $weekDay])->where('hour', $hour)->get();

        foreach($allDuties as &$duty){

            if(!$duty->isDutyInWeek($weekStartDate) || $duty->isSuspended($weekStartDate)) {
                unset($duty);
            }
        }

        return $allDuties->pluck('user_id');
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

    public function isSuspended(Carbon $weekStartDate): bool
    {
        $currentDate = $weekStartDate->addDays(Helper::dayNumber($this->day));

        $suspendFrom = Carbon::parse($this->suspend_from);
        $suspendTo = Carbon::parse($this->suspend_to);
        if($currentDate->between($suspendFrom, $suspendTo) || ($currentDate >= $suspendFrom && !$suspendTo)) {
            return true;
        }

        return false;
    }
}
