<?php

namespace App\Http\Controllers;

use App\Models\DutyPattern;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use WeekDays;

class DutyPatternController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display admin duty patterns.
     */
    public function index(): View
    {
        $patterns = DutyPattern::query()
            ->where('is_admin_duty', true)
            ->with('user')
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get()
            ->groupBy('user_id');

        $admins = User::query()
            ->where('is_admin', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return ViewFacade::make('duty-patterns.index', [
            'patterns' => $patterns,
            'admins' => $admins,
            'weekDays' => WeekDays::DAYS
        ]);
    }

    /**
     * Store a new duty pattern.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'hour' => ['required', 'integer', 'between:0,23'],
            'repeat_pattern' => ['required', 'in:weekly,biweekly,triweekly']
        ]);

        DutyPattern::query()->create([
            'user_id' => $validated['admin_id'],
            'day_of_week' => $validated['day_of_week'],
            'hour' => $validated['hour'],
            'repeat_pattern' => $validated['repeat_pattern'],
            'is_admin_duty' => true
        ]);

        return Redirect::route('duty-patterns.index')
            ->with('success', 'Wzorzec dyżuru został zapisany');
    }

    /**
     * Remove the specified duty pattern.
     */
    public function destroy(DutyPattern $pattern): RedirectResponse
    {
        if (!Auth::user()->is_admin) {
            return Redirect::route('duty-patterns.index')
                ->with('error', 'Brak uprawnień do usunięcia tego wzorca');
        }

        $pattern->delete();

        return Redirect::route('duty-patterns.index')
            ->with('success', 'Wzorzec dyżuru został usunięty');
    }

    /**
     * Update the specified duty pattern.
     */
    public function update(Request $request, DutyPattern $pattern): RedirectResponse
    {
        if (!Auth::user()->is_admin) {
            return Redirect::route('duty-patterns.index')
                ->with('error', 'Brak uprawnień do edycji tego wzorca');
        }

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'hour' => ['required', 'integer', 'between:0,23'],
            'repeat_pattern' => ['required', 'in:weekly,biweekly,triweekly']
        ]);

        $pattern->update($validated);

        return Redirect::route('duty-patterns.index')
            ->with('success', 'Wzorzec dyżuru został zaktualizowany');
    }
}
