<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatternStoreRequest;
use App\Http\Requests\SuspendDutyRequest;
use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\DutiesService;
use App\Services\Helper;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class PatternController extends Controller
{
    /**
     * The notification service instance.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new controller instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display the duty calendar.
     */
    public function index(): View
    {
        $patterns = DutyPattern::query()
        ->select(['hour', 'day', 'id','duty_type', 'repeat_interval'])
        ->where('user_id', Auth::user()->id)
        ->get()
        ->groupBy('duty_type');

        return ViewFacade::make('patterns.index', [
            'duties'    => $patterns,
            'weekDays'  => Helper::getWeekDays(),
            'hours'     => Helper::getHours(),
            'intervals' => Helper::getIntervals(),
        ]);
    }

    public function suspend(SuspendDutyRequest $request)
    {
        $data = $request->validated();
        $dutyPattern = DutyPattern::find($data['id']);

        if($dutyPattern->user_id !== Auth::user()->id){
            return;
        }

        // $dutyPattern->sus
    }

    /**
     * Store a new duty.
     */
    public function store(PatternStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = Auth::user();

        $duty = DutyPattern::query()->create([
            'user_id'         => $user->id,
            'day'             => $validated['day'],
            'hour'            => $validated['hour'],
            'duty_type'       => $validated['duty_type'],
            'repeat_interval' => $validated['repeat_interval'],
            'start_date' => $validated['start_date'] ?? Carbon::now()
        ]);

        DutiesService::updateUserDuties($user);

        return Redirect::route('patterns.index')
            ->with('success', 'Dyżur został zapisany pomyślnie');
    }

    /**
     * Remove a duty.
     */
    public function destroy(DutyPattern $dutyPattern): RedirectResponse
    {

        if (($dutyPattern->user_id != Auth::id() && (!Auth::user()->is_admin))) {
            return Redirect::route('patterns.index')
                ->with('error', 'Brak uprawnień do usunięcia tego dyżuru');
        }

        $dutyPattern->delete();

        DutiesService::updateUserDuties(Auth::user());

        return Redirect::route('patterns.index')
            ->with('success', 'Dyżur został usunięty pomyślnie');
    }

    /**
     * Notify admin and reserve users if no one is assigned.
     */
    private function notifyIfNoAssignment(CurrentDuty $duty): void
    {
        $hasReplacement = CurrentDuty::query()
            ->where('duty_date', $duty->duty_date)
            ->where('hour', $duty->hour)
            ->exists();

        if (! $hasReplacement) {
            $admin = User::query()
                ->whereHas('dutyPatterns', function ($query) use ($duty) {
                    $query->where('day_of_week', Carbon::parse($duty->duty_date)->dayOfWeek)
                        ->where('hour', $duty->hour)
                        ->where('is_admin_duty', true);
                })->first();

            $reserveUsers = User::query()
                ->whereHas('reservePatterns', function ($query) use ($duty) {
                    $query->where('day_of_week', Carbon::parse($duty->duty_date)->dayOfWeek)
                        ->where('hour', $duty->hour);
                })->get();

            $message = sprintf(
                'Brak użytkownika na dyżurze w dniu %s o godzinie %d:00.',
                Carbon::parse($duty->duty_date)->format('d.m.Y'),
                $duty->hour
            );

            if ($admin) {
                $this->notificationService->sendNotification($admin, $message);
            }

            foreach ($reserveUsers as $user) {
                $this->notificationService->sendNotification($user, $message);
            }
        }
    }

    private function getAdminDuty()
    {
        $hours = Helper::getPatterns(['admin']);

        $adminDuties = DB::table('admin_duty_patterns as adp')
            ->select(['u.first_name', 'u.last_name', 'adp.hour', 'adp.day'])
            ->leftJoin('users as u', 'u.id', 'adp.admin_id')
            ->get()
            ->toArray();

        foreach ($adminDuties as $duty) {
            $hours[$duty->day][$duty->hour]['admin'] = $duty->first_name . ' ' . $duty->last_name;
        }

        return $hours;
    }
}
