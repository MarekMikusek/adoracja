<?php

namespace App\Services;

use Carbon\Carbon;

class Helper
{
    const WEEK_DAYS = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
    const DAY_HOURS = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
    /**
     * returns week hours in format [['date' => 1, 'dayName' => 'Poniedziałek', 'hour' => 0], [] , ...]
     *
     * @param Carbon $startDate
     * @return array
     */
    public static function generateWeekHours(Carbon $startDate): array
    {
        $weekHours = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startDate->addDays($i);
            $dayName = $day->translatedFormat('l');
            foreach(range(0, 23, 1) as $hour){
                $weekHours[]= ['date' => $day->format('j'), 'dayName' => $dayName, 'hour' => $hour];
            }
        }

        return $weekHours;
    }

        /**
     * returns week hours in format [['date' => 1, ''dayName' => 'Poniedziałek', 'hour' => 0], [] , ...]
     *
     * @param Carbon $startDate
     * @return array
     */
    public static function generateTimeFrames(): array
    {
        $key = 1;
        $weekHours = [];
        foreach (self::WEEK_DAYS as $day) {
            foreach(range(0, 23, 1) as $hour){
                $weekHours[$key++]= ['day' => $day, 'hour' => $hour];
            }
        }

        return $weekHours;
    }

    public static function getWeekDays()
    {
        return self::WEEK_DAYS;
    }

    public static function getHours()
    {
        return self::DAY_HOURS;
    }

    public static function getPatterns(array $patternKeys): array
    {
        $res = [];
        foreach (self::WEEK_DAYS as $day)
        {
            $res[$day] = [];
            foreach(self::DAY_HOURS as $hour){
                $res[$day][$hour] = [];
                foreach($patternKeys as $patternKey){
                    $res[$day][$hour][$patternKey] = null;
                }
            }
        }
        return $res;
    }

    public static function getIntervals(): array
    {
        return [
            1 => ['name' => 'co tydzień', 'value'=> 1],
            2 => ['name' => 'co 2 tygodnie', 'value'=> 2],
            3 => ['name' => 'co 3 tygodnie', 'value'=> 3],
        ];
    }

    public static function dayNumber($dayName): int
    {
        return array_search($dayName, self::WEEK_DAYS);
    }
}
