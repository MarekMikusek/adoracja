<?php
namespace App\Services;

use App\Models\CurrentDuty;
use Carbon\Carbon;

class DateHelper
{
    public static function dayOfWeek(string $date)
    {
        return mb_ucfirst(Carbon::createFromDate($date)->isoFormat('dddd'));
    }

    public static function weekDayOffset($weekDay)
    {
        switch (strtolower($weekDay)) {
            case 'niedziela':
                return 0;
            case 'poniedziałek':
                return 1;
            case 'wtorek':
                return 2;
            case 'środa':
                return 3;
            case 'czwartek':
                return 4;
            case 'piątek':
                return 5;
            case 'sobota':
                return 6;
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
