<?php
namespace App\Http\Controllers;

use App\Enums\DutyType;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;

class HomeController extends Controller
{
    public const ADORACJA_COLOUR = '#328E6E';
    public const REZERWA_COLOUR = '#D3CA79';
    public const NO_DUTY_COLOUR = '#F7374F';
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index']);
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $userId = null;

        if ($user = Auth::user()) {
            $userId = Auth::user()->id;
        }

        $upcomingDuties = DB::table('current_duties as cd')
            ->selectRaw('cd.date, cd.hour, cdu.user_id, cd.id as duty_id, cdu.duty_type')
            ->where('date', '>=', Carbon::today())
            ->join('current_duties_users as cdu', 'cdu.current_duty_id', '=', 'cd.id', 'full')
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();
// dd($upcomingDuties);
        $duties = [];
        foreach ($upcomingDuties as $duty) {
            $dateFormatted = Carbon::createFromDate($duty->date)->isoFormat('D MMMM');

            if (! isset($duties[$dateFormatted])) {

                $duties[$dateFormatted]               = [];
                $duties[$dateFormatted]['dayName']    = Carbon::createFromDate($duty->date)->isoFormat('dddd');
                $duties[$dateFormatted]['timeFrames'] = [];

                foreach (Helper::DAY_HOURS as $hour) {
                    $duties[$dateFormatted]['timeFrames'][$hour]['hour']         = $duty->hour;
                    $duties[$dateFormatted]['timeFrames'][$hour]['adoracja']     = 0;
                    $duties[$dateFormatted]['timeFrames'][$hour]['rezerwa']   = 0;
                    $duties[$dateFormatted]['timeFrames'][$hour]['userDutyType'] = '';
                }
            }
            $duties[$dateFormatted]['timeFrames'][$duty->hour]['dutyId'] = $duty->duty_id;

            if (isset($userId) && $duty->user_id && $duty->user_id == $user->id && $duty->duty_type != DutyType::SUSPEND) {
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['userDutyType'] = $duty->duty_type;
            }

            if ($duty->user_id && $duty->duty_type == 'adoracja') {
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['adoracja']++;
            }

            if ($duty->user_id && $duty->duty_type == 'rezerwa') {
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['rezerwa']++;
            }

        }

        return ViewFacade::make('home', [
            'user'     => $user,
            'duties'   => $duties,
            'dayHours' => Helper::DAY_HOURS,
            'adoracjaColour' => self::ADORACJA_COLOUR,
            'rezerwaColour' => self::REZERWA_COLOUR,
            'noDutyColour' => self::NO_DUTY_COLOUR
        ]);
    }
}
