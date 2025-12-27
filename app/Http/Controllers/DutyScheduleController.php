<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use WeekDays;

class DutyScheduleController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display duty schedule.
     */
    public function index(Request $request): View
    {
        $startDate = Carbon::parse($request->get('start_date', 'today'));
        $endDate = $startDate->copy()->addDays(6);

        $duties = CurrentDuty::query()
            ->with(['user', 'admin'])
            ->whereBetween('duty_date', [$startDate, $endDate])
            ->get()
            ->groupBy(['duty_date', 'hour']);

        $patterns = DutyPattern::query()
            ->where('is_admin_duty', true)
            ->with('user')
            ->get()
            ->groupBy(['day_of_week', 'hour']);

        return ViewFacade::make('duty-schedule.index', [
            'duties' => $duties,
            'patterns' => $patterns,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'weekDays' => WeekDays::DAYS
        ]);
    }

    /**
     * Display user's schedule.
     */
    public function mySchedule(): View
    {
        $upcomingDuties = CurrentDuty::query()
            ->where('user_id', Auth::id())
            ->where('duty_date', '>=', Carbon::today())
            ->orderBy('duty_date')
            ->orderBy('hour')
            ->get();

        $patterns = DutyPattern::query()
            ->where('user_id', Auth::id())
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get()
            ->groupBy('day_of_week');

        return ViewFacade::make('duty-schedule.my-schedule', [
            'upcomingDuties' => $upcomingDuties,
            'patterns' => $patterns,
            'weekDays' => WeekDays::DAYS
        ]);
    }

    /**
     * Get available slots for duty assignment.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return Collection
     */
    private function getAvailableSlots(Carbon $startDate, Carbon $endDate): Collection
    {
        $takenSlots = CurrentDuty::query()
            ->whereBetween('duty_date', [$startDate, $endDate])
            ->get(['duty_date', 'hour'])
            ->groupBy(['duty_date', 'hour']);

        $patterns = DutyPattern::query()
            ->where('is_admin_duty', true)
            ->with('user')
            ->get()
            ->groupBy(['day_of_week', 'hour']);

        $availableSlots = new Collection();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            foreach (range(0, 23) as $hour) {
                $dateString = $date->format('Y-m-d');

                if (!isset($takenSlots[$dateString][$hour]) &&
                    isset($patterns[$date->dayOfWeek][$hour])) {
                    $availableSlots->push([
                        'date' => $dateString,
                        'hour' => $hour,
                        'admin' => $patterns[$date->dayOfWeek][$hour]->first()->user
                    ]);
                }
            }
        }

        return $availableSlots;
    }

    /**
     * Take available duty slot.
     */
    public function takeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after:today'],
            'hour' => ['required', 'integer', 'between:0,23'],
            'admin_id' => ['required', 'exists:users,id']
        ]);

        // Check if slot is still available
        $exists = CurrentDuty::query()
            ->where('duty_date', $validated['date'])
            ->where('hour', $validated['hour'])
            ->exists();

        if ($exists) {
            return Redirect::back()
                ->with('error', 'Ten termin został już zajęty');
        }

        $duty = CurrentDuty::create([
            'user_id' => Auth::id(),
            'admin_id' => $validated['admin_id'],
            'duty_date' => $validated['date'],
            'hour' => $validated['hour']
        ]);

        // Send notification to admin
        $this->notificationService->sendNotification(
            $duty->admin,
            "Użytkownik zapisał się na dyżur w dniu {$duty->duty_date} godz. {$duty->hour}:00"
        );

        return Redirect::route('duty-schedule.my-schedule')
            ->with('success', 'Zostałeś zapisany na dyżur');
    }
}
