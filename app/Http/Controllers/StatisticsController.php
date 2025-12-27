<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display duty statistics.
     */
    public function index(Request $request): View
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($request->has('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
        }
        if ($request->has('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        $statistics = $this->getDutyStatistics($startDate, $endDate);
        $monthlyStats = $this->getMonthlyStatistics();
        $topUsers = $this->getTopUsers($startDate, $endDate);

        return ViewFacade::make('statistics.index', [
            'statistics' => $statistics,
            'monthlyStats' => $monthlyStats,
            'topUsers' => $topUsers,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString()
        ]);
    }

    /**
     * Get duty statistics for the given period.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return array<string, mixed>
     */
    private function getDutyStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $duties = CurrentDuty::query()
            ->whereBetween('duty_date', [$startDate, $endDate])
            ->get();

        $totalDuties = $duties->count();
        $completedDuties = $duties->where('completed', true)->count();
        $missedDuties = $duties->where('completed', false)->count();

        return [
            'total' => $totalDuties,
            'completed' => $completedDuties,
            'missed' => $missedDuties,
            'completion_rate' => $totalDuties > 0 
                ? round(($completedDuties / $totalDuties) * 100, 2) 
                : 0
        ];
    }

    /**
     * Get monthly statistics for the past year.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getMonthlyStatistics(): Collection
    {
        return DB::table('current_duties')
            ->select(
                DB::raw('DATE_FORMAT(duty_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed')
            )
            ->where('duty_date', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->completion_rate = $item->total > 0 
                    ? round(($item->completed / $item->total) * 100, 2) 
                    : 0;
                return $item;
            });
    }

    /**
     * Get top users by number of completed duties.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopUsers(Carbon $startDate, Carbon $endDate): Collection
    {
        return User::query()
            ->select('users.*')
            ->addSelect(DB::raw('COUNT(current_duties.id) as duties_count'))
            ->addSelect(DB::raw('SUM(CASE WHEN current_duties.completed = 1 THEN 1 ELSE 0 END) as completed_count'))
            ->leftJoin('current_duties', 'users.id', '=', 'current_duties.user_id')
            ->whereBetween('current_duties.duty_date', [$startDate, $endDate])
            ->groupBy('users.id')
            ->orderByDesc('completed_count')
            ->limit(10)
            ->get();
    }
} 