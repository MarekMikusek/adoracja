<?php
namespace App\Http\Controllers;

use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class HomeController extends Controller
{
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
    public function index(): View
    {
        $userId = null;

        if ($user = Auth::user()) {
            $userId = Auth::user()->id;
        }


        $upcomingDuties = DB::table('current_duties as cd')
            ->select(['cd.date', 'cd.hour', 'cdu.user_id', 'cd.id'])
            ->where('date', '>=', Carbon::today())
            ->leftJoin('current_duties_users as cdu', 'cdu.current_duty_id', 'cd.id')
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();

        $duties = [];
        foreach ($upcomingDuties as $duty) {
            $dateFormatted = Carbon::createFromDate($duty->date)->isoFormat('D MMMM');

            if (! isset($duties[$dateFormatted])) {

                $duties[$dateFormatted]               = [];
                $dayName                              = Carbon::createFromDate($duty->date)->isoFormat('dddd');
                $duties[$dateFormatted]['dayName']    = $dayName;
                $duties[$dateFormatted]['timeFrames'] = [];
                foreach (Helper::DAY_HOURS as $hour) {
                    $duties[$dateFormatted]['timeFrames'][$hour]['hour']       = $hour;
                    $duties[$dateFormatted]['timeFrames'][$hour]['dutyId']       = $duty->id;
                    $duties[$dateFormatted]['timeFrames'][$hour]['users']      = [];
                    $duties[$dateFormatted]['timeFrames'][$hour]['isUserDuty'] = false;
                }
            }

            if ($duty->user_id) {
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['users'][] = $duty->user_id;
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['isUserDuty'] = ($userId && $user->id == $duty->user_id);
            }
        }
// dd($duties);
        return ViewFacade::make('home', [
            'user' => $user,
            'duties' => $duties,
            'dayHours' => Helper::DAY_HOURS,
            // 'repeatPatternLabels' => $this->repeatPatternLabels,
        ]);
    }
}
