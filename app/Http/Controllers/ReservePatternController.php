<?php

namespace App\Http\Controllers;

use App\Models\ReservePattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class ReservePatternController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display reserve patterns.
     */
    public function index(): View
    {
        $patterns = ReservePattern::query()
            ->where('user_id', Auth::id())
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get()
            ->groupBy('day_of_week');

        return ViewFacade::make('reserve-patterns.index', [
            'patterns' => $patterns,
            'weekDays' => Helper::WEEK_DAYS,
            'repeatPatterns' => [
                'weekly' => 'Co tydzień',
                'biweekly' => 'Co 2 tygodnie',
                'triweekly' => 'Co 3 tygodnie'
            ]
        ]);
    }

    /**
     * Store a new reserve pattern.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'hour' => ['required', 'integer', 'between:0,23'],
            'repeat_pattern' => ['required', 'in:weekly,biweekly,triweekly']
        ]);

        // Check for existing pattern
        $exists = ReservePattern::query()
            ->where('user_id', Auth::id())
            ->where('day_of_week', $validated['day_of_week'])
            ->where('hour', $validated['hour'])
            ->exists();

        if ($exists) {
            return Redirect::route('reserve-patterns.index')
                ->with('error', 'Wzorzec dla tej godziny już istnieje');
        }

        ReservePattern::query()->create([
            'user_id' => Auth::id(),
            'day_of_week' => $validated['day_of_week'],
            'hour' => $validated['hour'],
            'repeat_pattern' => $validated['repeat_pattern']
        ]);

        return Redirect::route('reserve-patterns.index')
            ->with('success', 'Wzorzec dyżuru rezerwowego został zapisany');
    }

    /**
     * Update the specified reserve pattern.
     */
    public function update(Request $request, ReservePattern $pattern): RedirectResponse
    {
        if ($pattern->user_id !== Auth::id()) {
            return Redirect::route('reserve-patterns.index')
                ->with('error', 'Brak uprawnień do edycji tego wzorca');
        }

        $validated = $request->validate([
            'repeat_pattern' => ['required', 'in:weekly,biweekly,triweekly']
        ]);

        $pattern->update($validated);

        return Redirect::route('reserve-patterns.index')
            ->with('success', 'Wzorzec dyżuru rezerwowego został zaktualizowany');
    }

    /**
     * Remove the specified reserve pattern.
     */
    public function destroy(ReservePattern $pattern): RedirectResponse
    {
        if ($pattern->user_id !== Auth::id()) {
            return Redirect::route('reserve-patterns.index')
                ->with('error', 'Brak uprawnień do usunięcia tego wzorca');
        }

        $pattern->delete();

        return Redirect::route('reserve-patterns.index')
            ->with('success', 'Wzorzec dyżuru rezerwowego został usunięty');
    }

    /**
     * Get available reserve slots.
     */
    public function getAvailableSlots(Request $request): View
    {
        $startDate = Carbon::parse($request->get('start_date', 'today'));
        $endDate = $startDate->copy()->addDays(6);

        $patterns = ReservePattern::query()
            ->with('user')
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get()
            ->groupBy(['day_of_week', 'hour']);

        return ViewFacade::make('reserve-patterns.slots', [
            'patterns' => $patterns,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
