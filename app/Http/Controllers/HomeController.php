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
    public function index()
    {
        $userId = null;

        if ($user = Auth::user()) {
            $userId = Auth::user()->id;
        }

        if($user && $user->is_admin == true){
            return response()->redirectTo('/admin/dashboard');
        }

        $upcomingDuties = DB::table('current_duties as cd')
            ->select(['cd.date', 'cd.hour', 'cdu.user_id', 'cd.id as duty_id', 'cdu.duty_type'])
            ->where('date', '>=', Carbon::today())
            ->leftJoin('current_duties_users as cdu', 'cdu.current_duty_id', 'cd.id')
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();

        $duties = [];
        foreach ($upcomingDuties as $duty) {
            $dateFormatted = Carbon::createFromDate($duty->date)->isoFormat('D MMMM');
// dump($duty);
            if (! isset($duties[$dateFormatted])) {

                $duties[$dateFormatted]               = [];
                $duties[$dateFormatted]['dayName']    = Carbon::createFromDate($duty->date)->isoFormat('dddd');
                $duties[$dateFormatted]['timeFrames'] = [];
            }
            $duties[$dateFormatted]['timeFrames'][$duty->hour]['hour']           = $duty->hour;
            $duties[$dateFormatted]['timeFrames'][$duty->hour]['dutyId']         = $duty->duty_id;
            $duties[$dateFormatted]['timeFrames'][$duty->hour]['users']          = [];
            $duties[$dateFormatted]['timeFrames'][$duty->hour]['userDutyType'] = '';

            if (isset($userId) && $duty->user_id && $duty->user_id == $user->id) {
                    $duties[$dateFormatted]['timeFrames'][$duty->hour]['userDutyType'] = $duty->duty_type;
            }

            if($duty->user_id && $duty->duty_type == 'adoracja'){
                $duties[$dateFormatted]['timeFrames'][$duty->hour]['users'][] = $duty->user_id;
            }

        }
        // dd($duties);
        return ViewFacade::make('home', [
            'user'     => $user,
            'duties'   => $duties,
            'dayHours' => Helper::DAY_HOURS,
        ]);
    }
}
