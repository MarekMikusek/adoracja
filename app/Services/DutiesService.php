<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DutyType;
use App\Models\CurrentDuty;
use App\Models\CurrentDutyUser;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DutiesService
{
    public static function getCurrentDutyMostDistantDate(): Carbon
    {
        return Carbon::createFromDate(DB::table('current_duties')->max('date'));
    }

    public static function suspend(User $user)
    {
        if (! $user->suspend_from) {
            return;
        }

        CurrentDutyUser::where('user_id', $user->id)
            ->whereHas('currentDuty', function ($query) use ($user) {
                $query->where('date', '>=', $user->suspend_from);

                if ($user->suspend_to) {
                    $query->where('date', '<=', $user->suspend_to);
                }
            })
            ->delete();
    }

    public static function removeSuspention(User $user)
    {
        CurrentDutyUser::withTrashed()
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->whereHas('currentDuty', function ($query) use ($user) {
                $query->where('date', '>=', Carbon::now());

                if ($user->suspend_to) {
                    $query->where('date', '<=', $user->suspend_to);
                }
            })
            ->restore();
    }

    public static function addUserDuties(User $user, DutyPattern $dutyPattern)
    {
        $targetDayIndex = DateHelper::weekDayOffset($dutyPattern->day);

        if (! $targetDayIndex) {
            return;
        }

        $duties = CurrentDuty::where('hour', $dutyPattern->hour)
            ->where('date', '>=', $dutyPattern->start_date)
            ->whereRaw('DAYOFWEEK(date) = ?', [$targetDayIndex])
            ->get();

        $inserts = [];

        foreach ($duties as $duty) {
            if ($dutyPattern->repeat_interval > 1) {
                $start   = Carbon::parse($dutyPattern->start_date);
                $current = Carbon::parse($duty->date);

                $diffInWeeks = $start->diffInWeeks($current);

                if ($diffInWeeks % $dutyPattern->repeat_interval !== 0) {
                    continue;
                }
            }

            $inserts[] = [
                'current_duty_id' => $duty->id,
                'user_id'         => $user->id,
                'duty_type'       => $dutyPattern->duty_type,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        if (! empty($inserts)) {
            CurrentDutyUser::insert($inserts);
        }

    }

    public static function removeUserDuties(DutyPattern $dutyPattern, User $user)
    {
        $targetDayIndex = DateHelper::weekDayOffset($dutyPattern->day);

        if (! $targetDayIndex) {
            return;
        }

        $duties = CurrentDuty::where('hour', $dutyPattern->hour)
            ->where('date', '>=', $dutyPattern->start_date)
            ->whereRaw('DAYOFWEEK(date) = ?', [$targetDayIndex])
            ->get();

        $dutyIdsToRemoval = [];

        foreach ($duties as $duty) {
            if ($dutyPattern->repeat_interval > 1) {
                $start   = Carbon::parse($dutyPattern->start_date);
                $current = Carbon::parse($duty->date);

                $diffInWeeks = $start->diffInWeeks($current);

                if ($diffInWeeks % $dutyPattern->repeat_interval !== 0) {
                    continue;
                }
            }

            $dutyIdsToRemoval[] = $duty->id;
        }

        if (! empty($dutyIdsToRemoval)) {
            CurrentDutyUser::where('user_id', $user->id)
                ->where('duty_type', $dutyPattern->duty_type)
                ->whereIn('current_duty_id', $dutyIdsToRemoval)
                ->delete();
        }
    }

    public static function updateUserDuties(User $user)
    {
        $startDate = Carbon::today();

        CurrentDutyUser::where('user_id', $user->id)
            ->where('changed_by', Helper::SYSTEM_USER)
            ->whereHas('currentDuty', function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate->toDateString());
            })
            ->delete();

        $patterns = DutyPattern::where('user_id', $user->id)->get();

        if ($patterns->isEmpty()) {
            return;
        }

        $patternHours = $patterns->pluck('hour')->unique();

        $availableDuties = CurrentDuty::where('date', '>=', $startDate->toDateString())
            ->whereIn('hour', $patternHours)
            ->where('inactive', 0)
            ->orderBy('date')
            ->get();

        $inserts = [];

        foreach ($availableDuties as $duty) {
            $date = Carbon::parse($duty->date);

            if ($user->suspend_from && $user->suspend_to) {
                $from = Carbon::parse($user->suspend_from)->startOfDay();
                $to   = Carbon::parse($user->suspend_to)->endOfDay();

                if ($date->between($from, $to)) {
                    continue;
                }
            }

            $dayName = mb_convert_case($date->translatedFormat('l'), MB_CASE_TITLE, "UTF-8");

            $matchingPatterns = $patterns->where('day', $dayName)
                ->where('hour', $duty->hour);

            foreach ($matchingPatterns as $pattern) {
                if ($pattern->start_date && $date->lt(Carbon::parse($pattern->start_date))) {
                    continue;
                }

                $isCorrectWeek = true;
                if ($pattern->repeat_interval > 1) {
                    $start       = Carbon::parse($pattern->start_date ?? $pattern->created_at)->startOfWeek();
                    $diffInWeeks = $start->diffInWeeks($date->copy()->startOfWeek());

                    if ($diffInWeeks % $pattern->repeat_interval !== 0) {
                        $isCorrectWeek = false;
                    }
                }

                if ($isCorrectWeek) {
                    $inserts[] = [
                        'user_id'         => $user->id,
                        'current_duty_id' => $duty->id,
                        'duty_type'       => $pattern->duty_type,
                        'changed_by'      => Helper::SYSTEM_USER,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }

        if (! empty($inserts)) {
            CurrentDutyUser::insert($inserts);
        }
    }

    public static function getCurrentDuties(Carbon $startDate)
    {
        $currentDuties = CurrentDuty::where('date', '>=', $startDate->format('Y-m-d'))->get();
        $ret           = [];
        foreach ($currentDuties as $duty) {
            $date = Carbon::create($duty->date)->format('Y-m-d');

            if (! isset($ret[$date])) {
                $ret[$date] = [];
            }
            $ret[$date][$duty->hour] = $duty;
        }
        return $ret;
    }

    public static function generateCurrentDuties(Collection $users, int $changedBy, $startDate = null, $noOfWeeks = 4)
    {
        $dateInserting = ($startDate ?: Carbon::now())->copy();

        $totalDays = $noOfWeeks * 7;

        for ($i = 0; $i < $totalDays; $i++) {

            $rawDay  = $dateInserting->translatedFormat('l');
            $weekDay = mb_convert_case($rawDay, MB_CASE_TITLE, "UTF-8");

            foreach (Helper::DAY_HOURS as $hour) {
                $currentDuty = CurrentDuty::firstOrCreate([
                    'hour' => $hour,
                    'date' => $dateInserting->toDateString(),
                ]);

                $usersForTimeFrame = DutyPattern::getUsersForTimeFrame($dateInserting, $weekDay, $hour, $users);

                foreach ($usersForTimeFrame as $userDuty) {
                    $currentDuty->users()->attach($userDuty['user_id'], [
                        'duty_type'  => $userDuty['duty_type'],
                        'changed_by' => $changedBy,
                    ]);
                }
            }
            $dateInserting->addDay();
        }

    }

    public static function applySuspension(User $user, ?Carbon $suspendFrom, ?Carbon $suspendTo): void
    {
        CurrentDutyUser::where('user_id', $user->id)
            ->whereHas('currentDuty', function ($query) use ($suspendFrom, $suspendTo) {
                $query->where('date', '>=', $suspendFrom->toDateString());

                if ($suspendTo) {
                    $query->where('date', '<=', $suspendTo->toDateString());
                }
            })
            ->update(['duty_type' => DutyType::SUSPEND]);
    }

    public function createDuty(array $dutyData)
    {
        return DutyPattern::create([
            'user_id'        => Auth::id(),
            'day_of_week'    => $dutyData['day'],
            'hour'           => $dutyData['hour'],
            'duty_type'      => $dutyData['duty_type'],
            'repeat_pattern' => $dutyData['repeat_interval'],
        ]);
    }
}
