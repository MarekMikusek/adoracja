<?php
namespace App\Services;

use App\Enums\DutyType;
use App\Models\CurrentDuty;
use App\Models\CurrentDutyUser;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DutiesService
{
    public static function updateUserDuties(User $user)
    {
        $oldDuties = (new CurrentDutyUser())->findUserDuties($user)->get();
        foreach ($oldDuties as $duty) {
            $duty->delete();
        }

        $startDate        = Carbon::now();
        $currentDate      = clone ($startDate);
        $endDate          = Carbon::createFromDate((new CurrentDuty())->orderBy('date', 'DESC')->first()['date'])->endOfDay();
        $userDutyPatterns = DutyPattern::where('user_id', $user->id)->get();

        $userDutyPatternsArr = [];

        foreach ($userDutyPatterns as $pattern) {
            $userDutyPatternsArr[$pattern->day][$pattern->hour] = $pattern;
        }

        $userCurrentDutiesInserts = [];

        $allDuties = self::getCurrentDuties($startDate);
        $allDates  = DateHelper::getCurrentDutiesDatesFromDate($startDate);

        foreach ($allDates as $dateString) {

            $currentDateDayOfWeek = DateHelper::dayOfWeek(Carbon::create($dateString));

            if (isset($userDutyPatternsArr[$currentDateDayOfWeek]) && ! $user->isSuspended($currentDate)) {

                foreach ($userDutyPatternsArr[$currentDateDayOfWeek] as $hour => $duty) {

                    if ($duty->isDutyInWeek($currentDate)) {
                        $currentDutyId = $allDuties[$dateString][$hour]->id;
                        $userCurrentDutiesInserts[] = ['user_id' => $user->id, 'current_duty_id' => $currentDutyId, 'duty_type' => $duty->duty_type];
                    }
                }
            }
            $currentDate->addDays(1);
        }

        return (new CurrentDutyUser())->insert($userCurrentDutiesInserts);
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

    public static function generateCurrentDuties(Collection $users, $startDate = null, $noOfWeeks = 4)
    {
        if (! $startDate) {
            $startDate = Carbon::now();
        }

        $dateInserting = $startDate->copy();

        for ($week = 1; $week <= $noOfWeeks; $week++) {
            foreach (Helper::WEEK_DAYS as $weekDay) {
                foreach (Helper::DAY_HOURS as $hour) {

                    $currentDuty       = new CurrentDuty();
                    $currentDuty->hour = $hour;
                    $currentDuty->date = $dateInserting;
                    $currentDuty->save();

                    $usersForTimeFrame = DutyPattern::getUsersForTimeFrame($startDate, $weekDay, $hour, $users);

                    foreach ($usersForTimeFrame as $userDuty) {
                        $currentDuty->users()->attach($userDuty['user_id'], ['duty_type' => $userDuty['duty_type']]);
                    }
                }
                $dateInserting->addDays(1);
            }
        }
    }

    public static function applySuspension(User $user, ?Carbon $suspendFrom, ?Carbon $suspendTo): void
    {
        $duties = DB::table('current_duties_users as cdu')
        ->selectRaw('cdu.id as id')
        ->join('current_duties as cd', 'cd.id', 'cdu.current_duty_id')
        ->where('cdu.user_id', $user->id)
        ->where('cd.date', '>=', $suspendFrom)
        ->when($suspendTo, function($query) use ($suspendTo){
            return $query->where('cd.date','<=', $suspendTo);
        })
        ->get();

        foreach($duties as $duty){
            CurrentDutyUser::find($duty->id)->update(['duty_type' => DutyType::SUSPEND]);
        }
    }

}
