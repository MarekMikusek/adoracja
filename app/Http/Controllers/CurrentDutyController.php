<?php
namespace App\Http\Controllers;

use App\Http\Requests\RemoveCurrentDutyRequest;
use App\Http\Requests\StoreCurrentDutyRequest;
use App\Jobs\CurrentDutyAddedJob;
use App\Models\CurrentDuty;
use App\Models\CurrentDutyUser;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class CurrentDutyController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {}

    public function index(Request $request): View
    {
        $query = CurrentDuty::query()
            ->with('user')
            ->where('duty_date', '>=', Carbon::today());

        if (! Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        $duties = $query->orderBy('duty_date')
            ->orderBy('hour')
            ->paginate(20);

        return ViewFacade::make('duties.index', [
            'duties' => $duties,
        ]);
    }

    public function store(StoreCurrentDutyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $currentDuty = CurrentDuty::find($data['duty_id']);
        $currentDuty->users()->attach(Auth::user(), ['duty_type' => $data['duty_type']]);

        CurrentDutyAddedJob::dispatch(Auth::user(), $currentDuty, $data['duty_type']);

        return Redirect::route('home')
            ->with('success', 'Dyżur został utworzony');
    }

    public function destroy(RemoveCurrentDutyRequest $request): RedirectResponse
    {
        $duty = CurrentDutyUser::where('current_duty_id', $request->validated()['duty_id'])
        ->where('user_id', Auth::user()->id)
        ->delete();

        return Redirect::route('home')
            ->with('success', 'Dyżur został usunięty');
    }
}
