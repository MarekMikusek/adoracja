<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReserveStoreRequest;
use App\Models\DutyPattern;
use App\Models\ReservePattern;
use App\Services\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ReserveController extends Controller
{
    /**
     * The repeat pattern labels.
     *
     * @var array<string, string>
     */
    protected $repeatPatternLabels = [
        'weekly' => 'Co tydzień',
        'biweekly' => 'Co 2 tygodnie',
        'triweekly' => 'Co 3 tygodnie'
    ];

    /**
     * The repeat pattern colors.
     *
     * @var array<string, string>
     */
    protected $repeatPatternColors = [
        'weekly' => 'primary',
        'biweekly' => 'success',
        'triweekly' => 'info'
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the reserve patterns.
     */
    public function index(): View
    {
        $reserves = ReservePattern::where('user_id', Auth::id())
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get();

        return view('reserves.index', [
            'reserves' => $reserves,
            'weekDays' => Helper::WEEK_DAYS,
            'repeatPatternLabels' => $this->repeatPatternLabels,
            'repeatPatternColors' => $this->repeatPatternColors
        ]);
    }

    /**
     * Store a new reserve pattern.
     */
    public function store(ReserveStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DutyPattern::create([
            'user_id' => Auth::id(),
            'day_of_week' => $validated['day'],
            'hour' => $validated['hour'],
            'duty_type' => $validated['duty_type'],
            'repeat_pattern' => $validated['repeat_interval']
        ]);

        return redirect()
            ->route('patterns.index')
            ->with('success', 'Dyżur rezerwowy został zapisany');
    }

    /**
     * Remove a reserve pattern.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $reserve = ReservePattern::find($request->id);

        if (($reserve->user_id !== Auth::id() && ! Auth::user()->is_admin)) {

            return Redirect::route('patterns.index')
                ->with('error', 'Brak uprawnień do usunięcia tego dyżuru');
        }

        $reserve->delete();

        return Redirect::route('patterns.index')
            ->with('success', 'Dyżur został anulowany');
    }
}
