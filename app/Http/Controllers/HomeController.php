<?php
namespace App\Http\Controllers;

use App\Enums\DutyType;
use App\Models\AdminDutyPattern;
use App\Models\MonthlyCoordinatorPattern;
use App\Models\User;
use App\Services\DateHelper;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;

class HomeController extends Controller
{
    public const MY_DUTY_COLOUR  = '#A7C7E7';
    public const REZERWA_COLOUR  = '#FFE440';
    public const NO_DUTY_COLOUR  = '#FFFFFF';
    public const HAS_DUTY_COLOUR = '#98FB98';

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $userId = null;

        if ($user = Auth::user()) {
            $userId = Auth::user()->id;
        }

        $leftJoinQuery = DB::table('current_duties as cd')
            ->selectRaw('cd.date, cd.hour, cdu.user_id, cd.id as duty_id, cdu.duty_type')
            ->where('cd.date', '>=', Carbon::today())
            ->leftJoin('current_duties_users as cdu', 'cdu.current_duty_id', '=', 'cd.id');

        $rightJoinQuery = DB::table('current_duties_users as cdu')
            ->selectRaw('cd.date, cd.hour, cdu.user_id, cd.id as duty_id, cdu.duty_type')
            ->leftJoin('current_duties as cd', 'cd.id', '=', 'cdu.current_duty_id')
            ->where('cd.date', '>=', Carbon::today())
            ->whereNull('cd.id');

        $upcomingDuties = $leftJoinQuery
            ->unionAll($rightJoinQuery)
            ->orderBy('date')
            ->orderBy('hour')
            ->get();

        $adminDutyPatterns = MonthlyCoordinatorPattern::coordinatorsResponsible();

        $duties = [];
        foreach ($upcomingDuties as $duty) {
            $currentDateAsCarbon = Carbon::createFromDate($duty->date);
            $dateFormatted = $currentDateAsCarbon->isoFormat('D MMMM');

            if (! isset($duties[$dateFormatted])) {

                $duties[$dateFormatted]                  = [];
                $duties[$dateFormatted]['dayName']       = DateHelper::dayOfWeek($duty->date);
                $duties[$dateFormatted]['dateFormatted'] = Carbon::createFromDate($duty->date)->format('d.m');
                $duties[$dateFormatted]['timeFrames']    = [];

                foreach (Helper::DAY_HOURS as $hour) {
                    $duties[$dateFormatted]['timeFrames'][$hour]['hour']         = $duty->hour;
                    $duties[$dateFormatted]['timeFrames'][$hour]['adoracja']     = 0;
                    $duties[$dateFormatted]['timeFrames'][$hour]['userDutyType'] = '';
                    $duties[$dateFormatted]['timeFrames'][$hour]['adminName'] = $adminDutyPatterns[(int)$currentDateAsCarbon->format('j')] ?? null;
                }
            }
            $duties[$dateFormatted]['timeFrames'][$duty->hour]['dutyId'] = $duty->duty_id;

            if (isset($userId) && $duty->user_id && $duty->user_id == $user->id && $duty->duty_type != DutyType::SUSPEND) {
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['userDutyType'] = $duty->duty_type;
            }

            if ($duty->user_id && $duty->duty_type == 'adoracja') {
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['adoracja']++;
            }
        }
        // dd($duties);

        return ViewFacade::make('home', [
            'user'            => $user,
            'duties'          => $duties,
            'admins'   => collect(User::admins())->keyBy('id')->toArray(),
            'dayHours'        => Helper::DAY_HOURS,
            'myDutyColour'    => self::MY_DUTY_COLOUR,
            'myReserveColour' => self::REZERWA_COLOUR,
            'noDutyColour'    => self::NO_DUTY_COLOUR,
            'hasDutyColour'   => self::HAS_DUTY_COLOUR,
        ]);
    }

    public function rodo()
    {
        return ViewFacade::make('rodo');
    }

    public function mainCoordinator()
    {
        return ViewFacade::make('main-coordinator');
    }

}
