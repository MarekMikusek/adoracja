<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CalendarController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get duties for calendar.
     */
    public function getDuties(Request $request): JsonResponse
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $duties = CurrentDuty::query()
            ->with('user')
            ->whereBetween('duty_date', [$startDate, $endDate])
            ->get()
            ->groupBy(['duty_date', 'hour']);

        $patterns = $this->getPatternDuties($startDate, $endDate);

        return Response::json([
            'duties' => $duties,
            'patterns' => $patterns
        ]);
    }

    /**
     * Get pattern duties for the given period.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getPatternDuties(Carbon $startDate, Carbon $endDate): Collection
    {
        $patterns = DutyPattern::query()
            ->with('user')
            ->where('is_admin_duty', true)
            ->get();

        $patternDuties = new Collection();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            foreach ($patterns as $pattern) {
                if ($pattern->day_of_week === $date->dayOfWeek) {
                    $patternDuties->push([
                        'date' => $date->format('Y-m-d'),
                        'hour' => $pattern->hour,
                        'user' => $pattern->user,
                        'repeat_pattern' => $pattern->repeat_pattern
                    ]);
                }
            }
        }

        return $patternDuties;
    }

    /**
     * Get available slots for the given period.
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $takenSlots = CurrentDuty::query()
            ->whereBetween('duty_date', [$startDate, $endDate])
            ->get(['duty_date', 'hour'])
            ->groupBy(['duty_date', 'hour']);

        $adminPatterns = DutyPattern::query()
            ->where('is_admin_duty', true)
            ->get()
            ->groupBy(['day_of_week', 'hour']);

        $availableSlots = new Collection();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            for ($hour = 0; $hour < 24; $hour++) {
                $dateString = $date->format('Y-m-d');
                
                if (!isset($takenSlots[$dateString][$hour]) && 
                    isset($adminPatterns[$date->dayOfWeek][$hour])) {
                    $availableSlots->push([
                        'date' => $dateString,
                        'hour' => $hour,
                        'admin' => $adminPatterns[$date->dayOfWeek][$hour]->first()->user
                    ]);
                }
            }
        }

        return Response::json(['slots' => $availableSlots]);
    }

    /**
     * Get user's upcoming duties.
     */
    public function getUpcomingDuties(): JsonResponse
    {
        $duties = CurrentDuty::query()
            ->where('user_id', Auth::id())
            ->where('duty_date', '>=', Carbon::today())
            ->orderBy('duty_date')
            ->orderBy('hour')
            ->limit(5)
            ->get();

        return Response::json(['duties' => $duties]);
    }
} 