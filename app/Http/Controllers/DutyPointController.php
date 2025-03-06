<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutyPoint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;

class DutyPointController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, DispatchesJobs;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', SubstituteBindings::class]);
    }

    /**
     * Display duty points summary.
     */
    public function index(): View
    {
        $query = DutyPoint::query()
            ->with('user')
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id');

        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        $pointsSummary = $query->get();

        $monthlyPoints = DutyPoint::query()
            ->where('user_id', Auth::id())
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('points');

        return ViewFacade::make('duty-points.index', [
            'pointsSummary' => $pointsSummary,
            'monthlyPoints' => $monthlyPoints
        ]);
    }

    /**
     * Display user's point history.
     */
    public function history(): View
    {
        $points = DutyPoint::query()
            ->where('user_id', Auth::id())
            ->with('duty')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ViewFacade::make('duty-points.history', [
            'points' => $points
        ]);
    }

    /**
     * Award points for completed duties.
     */
    public function awardPoints(Request $request): RedirectResponse
    {
        if (!Auth::user()->is_admin) {
            return Redirect::back()
                ->with('error', 'Brak uprawnień do przyznawania punktów');
        }

        $validated = $request->validate([
            'duty_id' => ['required', 'exists:current_duties,id'],
            'points' => ['required', 'numeric', 'min:0', 'max:10'],
            'comment' => ['nullable', 'string', 'max:255']
        ]);

        $duty = CurrentDuty::findOrFail($validated['duty_id']);

        if (!$duty->completed) {
            return Redirect::back()
                ->with('error', 'Nie można przyznać punktów za nieukończony dyżur');
        }

        DB::transaction(function () use ($duty, $validated) {
            DutyPoint::create([
                'user_id' => $duty->user_id,
                'duty_id' => $duty->id,
                'points' => $validated['points'],
                'comment' => $validated['comment'],
                'awarded_by' => Auth::id()
            ]);

            // Update user's total points
            $duty->user->increment('total_points', $validated['points']);
        });

        return Redirect::back()
            ->with('success', 'Punkty zostały przyznane');
    }

    /**
     * Display points leaderboard.
     */
    public function leaderboard(): View
    {
        $monthlyLeaders = User::query()
            ->select('users.*', DB::raw('COALESCE(SUM(duty_points.points), 0) as monthly_points'))
            ->leftJoin('duty_points', function ($join) {
                $join->on('users.id', '=', 'duty_points.user_id')
                    ->where('duty_points.created_at', '>=', Carbon::now()->startOfMonth());
            })
            ->groupBy('users.id')
            ->orderByDesc('monthly_points')
            ->limit(10)
            ->get();

        $allTimeLeaders = User::query()
            ->select('users.*', DB::raw('COALESCE(SUM(duty_points.points), 0) as total_points'))
            ->leftJoin('duty_points', 'users.id', '=', 'duty_points.user_id')
            ->groupBy('users.id')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        return ViewFacade::make('duty-points.leaderboard', [
            'monthlyLeaders' => $monthlyLeaders,
            'allTimeLeaders' => $allTimeLeaders
        ]);
    }
} 