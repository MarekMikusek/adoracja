<?php
namespace App\Services;

use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DutiesService
{
    public static function generateCurrentDuties(Collection $users, $startDate = null, $noOfWeeks = 4)
    {
        if (! $startDate) {
            $startDate = Carbon::now();
        }

        $dateInserted = $startDate->copy();

        for ($week = 1; $week <= $noOfWeeks; $week++) {
            foreach (Helper::WEEK_DAYS as $weekDay) {
                foreach (Helper::DAY_HOURS as $hour) {
                    $currentDuty       = new CurrentDuty();
                    $currentDuty->hour = $hour;
                    $currentDuty->date = $dateInserted;
                    $currentDuty->save();

                    $usersForTimeFrame = DutyPattern::getUsersForTimeFrame($startDate, $weekDay, $hour, $users);

                    foreach ($usersForTimeFrame as $userDuty) {
                        $currentDuty->users()->attach($userDuty['user_id'], ['duty_type' => $userDuty['duty_type']]);
                    }
                }
                $dateInserted->addDays(1);
            }
        }
    }

    public static function applySuspension(User $user): void
    {
        $duties = $user->currentDuties;

        foreach($duties as $duty) {
            $duty->users()->detach($user);
        }
    }

}
