<?php
namespace App\Http\Controllers;

use App\Enums\DutyType;
use App\Http\Requests\AssignAdminRequest;
use App\Http\Requests\ConfirmIntentionRequest;
use App\Http\Requests\RemoveIntentionRequest;
use App\Models\AdminDutyPattern;
use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Models\Intention;
use App\Models\ReservePattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use WeekDays;
use App\Services\DateHelper;
use App\Services\DutiesService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public const MY_DUTY_COLOUR = '#A7C7E7';
    public const REZERWA_COLOUR  = '#FFE440';
    public const NO_DUTY_COLOUR  = '#FFFFFF';
    public const HAS_DUTY_COLOUR  = '#98FB98';

    public function dashboard()
    {
        $adminDutyPatterns = AdminDutyPattern::adminDutyPatterns();

        $startDate = Carbon::now()->subDay();
        
        if($startDate->diffInWeeks(DutiesService::getCurrentDutyMostDistantDate()) < 5 ){
            Artisan::call('app:generate-current-duties --no_weeks=1');
        }

        $currentDuties = DB::table('current_duties as cd')
            ->selectRaw("cd.date, cd.hour, cdu.user_id, cd.id as duty_id, cdu.duty_type, u.first_name || ' ' || u.last_name as name")
            ->where('date', '>=', $startDate)
            ->leftJoin('current_duties_users as cdu', 'cdu.current_duty_id', 'cd.id')
            ->leftJoin('users as u', 'cdu.user_id', 'u.id')
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();

        $duties = [];

        foreach ($currentDuties as $duty) {
            $currentDate = Carbon::createFromDate($duty->date)->isoFormat('DD.MM');

            if (! isset($duties[$currentDate])) {
                $dayName                            = DateHelper::dayOfWeek($duty->date);
                $duties[$currentDate]               = [];
                $duties[$currentDate]['dayName']    = $dayName;
                $duties[$currentDate]['timeFrames'] = [];
            }

            if (! isset($duties[$currentDate]['timeFrames'][$duty->hour])) {
                $duties[$currentDate]['timeFrames'][$duty->hour] = [];
                $duties[$currentDate]['timeFrames'][$duty->hour][DutyType::DUTY->value]  = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour][DutyType::READY->value] = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour][DutyType::SUSPEND->value] = 0;
                $duties[$currentDate]['timeFrames'][$duty->hour]['admin_id'] = $adminDutyPatterns[$dayName][$duty->hour]['admin_id'] ?? null;
                $duties[$currentDate]['timeFrames'][$duty->hour]['duty_id'] = $duty->duty_id;
            }

            if ($duty->user_id) {
                $duties[$currentDate]['timeFrames'][$duty->hour][$duty->duty_type]++;
            }
        }

        return view('admin.dashboard', [
            'duties'   => $duties,
            'admins'   => collect(User::admins())->keyBy('id')->toArray(),
            'dayHours' => Helper::DAY_HOURS,
            'myDutyColour' => self::MY_DUTY_COLOUR,
            'myReserveColour'  => self::REZERWA_COLOUR,
            'noDutyColour'   => self::NO_DUTY_COLOUR,
            'hasDutyColour' => self::HAS_DUTY_COLOUR
        ]);
    }

    public function intentions()
    {
        return view('admin.intentions.index',['intentions' => Intention::orderBy('id', "DESC")->get() ?? []]);
    }

    public function confirmIntention(ConfirmIntentionRequest $request)
    {
        $intention = Intention::find($request->validated()['intention']);
        $intention->user_id = Auth::user()->id;

        return $intention->save();
    }

    public function removeIntention(RemoveIntentionRequest $request)
    {
        $data = $request->validated();

        DB::table('intentions_users')->where('intention_id', $data['intention'])->delete();

        DB::table('intentions')->where('id', $data['intention'])->delete();
    }

    public function hours(): View
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->with('dutyPatterns')
            ->get();

        $adminHours = $admins->mapWithKeys(function ($admin) {
            return [$admin->id => $admin->dutyPatterns];
        });

        return ViewFacade::make('admin.hours.index', [
            'admins'     => $admins,
            'adminHours' => $adminHours,
            'weekDays'   => WeekDays::DAYS,
        ]);
    }

    /**
     * Store admin duty hours.
     */
    public function storeHours(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
            'days'     => ['required', 'array'],
            'days.*'   => ['integer', 'between:0,6'],
            'hours'    => ['required', 'array'],
            'hours.*'  => ['integer', 'between:0,23'],
        ]);

        $admin = User::findOrFail($validated['admin_id']);

        // Clear existing patterns
        $admin->dutyPatterns()->delete();

        // Create new patterns
        foreach ($validated['days'] as $day) {
            foreach ($validated['hours'] as $hour) {
                $admin->dutyPatterns()->create([
                    'day_of_week'   => $day,
                    'hour'          => $hour,
                    'is_admin_duty' => true,
                ]);
            }
        }

        return Redirect::route('admin.hours')
            ->with('success', 'Godziny dyżurów zostały zapisane');
    }

    public function getAdminHours()
    {
        $adminHours = DutyPattern::where('is_admin_duty', true)
            ->with('user')
            ->get()
            ->groupBy('user_id');

        return response()->json(['adminHours' => $adminHours]);
    }

    public function getDutyUsers($date, $hour)
    {
        $duties = CurrentDuty::where('duty_date', $date)
            ->where('hour', $hour)
            ->with('user')
            ->get();

        $reserves = ReservePattern::where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->where('hour', $hour)
            ->with('user')
            ->get();

        return response()->json([
            'duties'   => $duties,
            'reserves' => $reserves,
        ]);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message'    => 'required|string',
        ]);

        // TODO: Implement notification sending

        return response()->json(['message' => 'Powiadomienia zostały wysłane']);
    }

    public function index()
    {
        $admins = User::where('is_admin', true)->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function updateDutyHours(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'duty_hours' => 'nullable|string',
        ]);

        $admin = User::findOrFail($request->admin_id);
        $admin->duty_hours = explode(',', $request->duty_hours);
        $admin->save();

        return response()->json(['message' => 'Duty hours updated successfully.']);
    }

    public function dutyHours()
    {
        $dutyHours = $this->getDutyHours();
        $admins = User::where('is_admin', true)->get();

        return view('admin.duty_hours', compact('dutyHours', 'admins'));
    }

    public function assignDutyHours(AssignAdminRequest $request)
    {
        $validated = $request->validated();

        DB::table('admin_duty_patterns')
            ->where('id', $validated['duty_id'])
            ->update(['admin_id' => $validated['admin_id']]);

        return response()->json(['message' => 'Admin assigned successfully.']);
    }

    public function getDutyHours()
    {
        $dutyHours = DB::table('admin_duty_patterns as adp')
        ->leftJoin('users as u', 'u.id', 'adp.admin_id')
        ->selectRaw("adp.day, adp.hour, adp.id, u.id as admin_id, concat(u.first_name, ' ', u.last_name) as admin_name")
        ->orderBy('adp.id')
        ->get();

        return $dutyHours;
    }

    public function updateColor(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'color' => 'required|string|size:7', // Ensure it's a valid hex color
        ]);

        $admin = User::findOrFail($request->admin_id);
        $admin->color = $request->color;
        $admin->save();

        return response()->json(['message' => 'Admin color updated successfully.']);
    }
}
