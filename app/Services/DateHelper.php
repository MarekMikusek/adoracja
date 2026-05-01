<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CurrentDuty;
use Carbon\Carbon;

class DateHelper
{
    public static function dayOfWeek(string $date)
    {
        return mb_ucfirst(Carbon::createFromDate($date)->locale('pl')->isoFormat('dddd'));
    }

    public static function weekDayOffset($weekDay)
    {
        switch (mb_strtolower($weekDay)) {
            case 'niedziela':
                return 1;
            case 'poniedziałek':
                return 2;
            case 'wtorek':
                return 3;
            case 'środa':
                return 4;
            case 'czwartek':
                return 5;
            case 'piątek':
                return 6;
            case 'sobota':
                return 7;
        }

    }

    public static function getCurrentDutiesDatesFromDate(Carbon $startDate)
    {
        $dates = CurrentDuty::select('date')
        ->where('date', '>=', $startDate->format('Y-m-d'))
        ->groupBy('date')
        ->get()
        ->pluck('date');
        $ret = [];
         foreach($dates as $date){
            $ret[] = $date->format('Y-m-d');
         }
         return $ret;
    }
}
