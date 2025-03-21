<?php

namespace App\Services;

use Carbon\Carbon;

class DateHelper
{
    public static function dayOfWeek(string $date)
    {
        return mb_ucfirst(Carbon::createFromDate($date)->isoFormat('dddd'));
    }
}