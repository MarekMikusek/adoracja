<?php
namespace App\Http\Controllers;

use App\Http\Requests\PatternStoreRequest;
use App\Http\Requests\SuspendDutyRequest;
use App\Mail\DutyCreatedMail;
use App\Mail\DutyRemovedMail;
use App\Models\DutyPattern;
use App\Services\DutiesService;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class PatternController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the duty calendar.
     */
    public function index(): View
    {
        $patterns = DutyPattern::query()
            ->select(['hour', 'day', 'id', 'duty_type', 'repeat_interval'])
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
        $data        = $request->validated();
        $dutyPattern = DutyPattern::find($data['id']);

        if ($dutyPattern->user_id !== Auth::user()->id) {
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

        /**@var \App\Models\User $user*/
        $user = auth()->user();

        $dutyPattern = DutyPattern::query()->create([
            'user_id'         => $user->id,
            'day'             => $validated['day'],
            'hour'            => $validated['hour'],
            'duty_type'       => $validated['duty_type'],
            'repeat_interval' => $validated['repeat_interval'],
            'start_date'      => $validated['start_date'] ?? Carbon::now(),
            'added_by'        => $user->id,
        ]);

        DutiesService::addUserDuties($user, $dutyPattern);

        if ($user->hasRealEmail()) {
            Mail::to($user->email)->send(new DutyCreatedMail($user, $dutyPattern));
        }

        return Redirect::route('patterns.index')
            ->with('success', 'Dyżur został zapisany pomyślnie');
    }

    /**
     * Remove a duty.
     */
    public function destroy(DutyPattern $dutyPattern): RedirectResponse
    {
        /**@var \App\Models\User $user*/
        $user = auth()->user();

        if (($dutyPattern->user_id != $user->id && (! Auth::user()->is_admin))) {
            return Redirect::route('patterns.index')
                ->with('error', 'Brak uprawnień do usunięcia tego dyżuru');
        }

        $dutyPattern->delete();

        DutiesService::removeUserDuties($dutyPattern, $user);

        if ($user->hasRealEmail()) {
            Mail::to($user->email)->send(new DutyRemovedMail($user, $dutyPattern));
        }

        return Redirect::route('patterns.index')
            ->with('success', 'Dyżur został usunięty pomyślnie');
    }

}
