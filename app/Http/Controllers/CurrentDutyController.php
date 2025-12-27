<?php
namespace App\Http\Controllers;

use App\Http\Requests\RemoveCurrentDutyRequest;
use App\Http\Requests\StoreCurrentDutyRequest;
use App\Http\Requests\StoreOnceDutyRequest;
use App\Jobs\CurrentDutyAddedJob;
use App\Models\CurrentDuty;
use App\Models\CurrentDutyUser;
use App\Models\User;
use App\Services\DateHelper;
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
        $duties = CurrentDutyUser::query()
            ->join('current_duties as cd', 'cd.id', 'current_duties_users.current_duty_id')
            ->join('users as u', 'u.id', 'current_duties_users.user_id')
            ->where('cd.date', '>=', Carbon::today())
            ->where('user_id', Auth::id())
            ->select(['current_duty_id', 'date', 'hour', 'duty_type', 'cd.inactive'])
            ->orderBy('duty_type')
            ->orderBy('date')
            ->orderBy('hour')
            ->get();

        foreach ($duties as $duty) {
            $duty['name_of_day'] = DateHelper::dayOfWeek($duty['date']);
        }

        $duties = $duties->groupBy('duty_type');
        $duties['adoracja'] = $duties['adoracja'] ?? [];
        $duties['lista_rezerwowa'] = $duties['rezerwa'] ?? [];

        unset($duties['rezerwa']);

        return ViewFacade::make('current_duties.index', [
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

    public function onceDuty(StoreOnceDutyRequest $request)
    {
        $data          = $request->validated();
        $currentDutyId = CurrentDuty::where('date', $data['date'])->where('hour', $data['hour'])->first()->id;

        $cdu = CurrentDutyUser::create([
            'current_duty_id' => $currentDutyId,
            'user_id'         => Auth::user()->id,
            'duty_type'       => $data['duty_type']]
        );
    }
}
