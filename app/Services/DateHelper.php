<?php
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
        switch (strtolower($weekDay)) {
            case 'niedziela':
                return 6;
            case 'poniedziałek':
                return 0;
            case 'wtorek':
                return 1;
            case 'środa':
                return 2;
            case 'czwartek':
                return 3;
            case 'piątek':
                return 4;
            case 'sobota':
                return 5;
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
