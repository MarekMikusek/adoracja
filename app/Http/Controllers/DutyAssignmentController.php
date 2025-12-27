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
use Illuminate\Support\Facades\DB;
use WeekDays;

class DutyAssignmentController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware(['auth', 'admin']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display duty assignment page.
     */
    public function index(): View
    {
        $users = User::query()
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $patterns = DutyPattern::query()
            ->where('is_admin_duty', true)
            ->with('user')
            ->get()
            ->groupBy('user_id');

        return ViewFacade::make('duty-assignments.index', [
            'users' => $users,
            'patterns' => $patterns,
            'weekDays' => WeekDays::DAYS
        ]);
    }

    /**
     * Generate automatic duty assignments.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'min_duties' => ['required', 'integer', 'min:0'],
            'max_duties' => ['required', 'integer', 'min:1']
        ]);

        DB::transaction(function () use ($validated) {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            // Get all active users
            $users = User::where('is_active', true)->get();
            $userDutyCounts = [];

            // Initialize duty counts
            foreach ($users as $user) {
                $userDutyCounts[$user->id] = 0;
            }

            // Get admin patterns
            $patterns = DutyPattern::where('is_admin_duty', true)
                ->with('user')
                ->get()
                ->groupBy(['day_of_week', 'hour']);

            // Generate duties
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                foreach (range(0, 23) as $hour) {
                    if (isset($patterns[$date->dayOfWeek][$hour])) {
                        $pattern = $patterns[$date->dayOfWeek][$hour]->first();
                        $admin = $pattern->user;

                        // Find user with least duties
                        $eligibleUsers = $users->filter(function ($user) use ($userDutyCounts, $validated) {
                            return $userDutyCounts[$user->id] < $validated['max_duties'];
                        });

                        if ($eligibleUsers->isEmpty()) {
                            continue;
                        }

                        $user = $eligibleUsers->sortBy(function ($user) use ($userDutyCounts) {
                            return $userDutyCounts[$user->id];
                        })->first();

                        // Create duty
                        CurrentDuty::create([
                            'user_id' => $user->id,
                            'duty_date' => $date->format('Y-m-d'),
                            'hour' => $hour,
                            'admin_id' => $admin->id
                        ]);

                        $userDutyCounts[$user->id]++;

                        // Send notification
                        $this->notificationService->sendNotification(
                            $user,
                            "Zostałeś przypisany do dyżuru w dniu {$date->format('Y-m-d')} o godzinie {$hour}:00"
                        );
                    }
                }
            }
        });

        return Redirect::route('duty-assignments.index')
            ->with('success', 'Dyżury zostały wygenerowane');
    }

    /**
     * Clear duty assignments.
     */
    public function clear(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date']
        ]);

        CurrentDuty::query()
            ->whereBetween('duty_date', [
                $validated['start_date'],
                $validated['end_date']
            ])
            ->delete();

        return Redirect::route('duty-assignments.index')
            ->with('success', 'Dyżury zostały wyczyszczone');
    }
}
