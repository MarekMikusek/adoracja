<?php
namespace App\Services;

use App\Enums\DutyType;
use App\Models\AdminDutyPattern;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public function getDuties(Carbon $startDate, string $adminName)
    {
        $currentDuties = DB::table('current_duties as cd')
            ->selectRaw("cd.date, cd.hour, cdu.user_id, cd.id as duty_id, cdu.duty_type, u.first_name || ' ' || u.last_name as name, cd.inactive as inactive")
            ->where('date', '>=', $startDate)
            ->leftJoin('current_duties_users as cdu', function ($join) {
                $join->on('cdu.current_duty_id', '=', 'cd.id')
                    ->whereNull('cdu.deleted_at');
            })
            ->leftJoin('users as u', 'cdu.user_id', 'u.id')
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();

        $duties = [];

        foreach ($currentDuties as $duty) {
            $currentDateAsCarbon = Carbon::createFromDate($duty->date);
            $currentDate         = $currentDateAsCarbon->isoFormat('DD.MM');

            if (! isset($duties[$currentDate])) {
                $dayName                            = DateHelper::dayOfWeek($duty->date);
                $duties[$currentDate]               = [];
                $duties[$currentDate]['dayName']    = $dayName;
                $duties[$currentDate]['timeFrames'] = [];
            }

            if (! isset($duties[$currentDate]['timeFrames'][$duty->hour])) {
                $duties[$currentDate]['timeFrames'][$duty->hour]                           = [];
                $duties[$currentDate]['timeFrames'][$duty->hour]['inactive']               = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour]['my_day']                 = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour][DutyType::DUTY->value]    = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour][DutyType::READY->value]   = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour][DutyType::SUSPEND->value] = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour]['admin_name']             = AdminDutyPattern::getAdmin($duty);
                $duties[$currentDate]['timeFrames'][$duty->hour]['duty_id']                = $duty->duty_id;
            }

            if ($duty->user_id) {
                $duties[$currentDate]['timeFrames'][$duty->hour][$duty->duty_type]++;
            }

            if ($adminName == $duties[$currentDate]['timeFrames'][$duty->hour]['admin_name']) {
                $duties[$currentDate]['timeFrames'][$duty->hour]['my_day'] = 1;
            }

            if ($duty->inactive == 1) {
                $duties[$currentDate]['timeFrames'][$duty->hour]['inactive'] = 1;
            }
        }
        return $duties;
    }
}
