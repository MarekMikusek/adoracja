<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrentDutyRequest;
use App\Models\CurrentDuty;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class CurrentDutyController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {}

    public function index(Request $request): View
    {
        $query = CurrentDuty::query()
            ->with('user')
            ->where('duty_date', '>=', Carbon::today());

        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        $duties = $query->orderBy('duty_date')
            ->orderBy('hour')
            ->paginate(20);

        return ViewFacade::make('duties.index', [
            'duties' => $duties
        ]);
    }

    public function store(StoreCurrentDutyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $currentDuty = CurrentDuty::find($data['duty_id']);
        $currentDuty->users()->attach(Auth::user(), ['duty_type' => $data['duty_type']]);

        // Send notification to the assigned user
        $user = Auth::user();
        // dd($user);
        $this->notificationService->sendNotification(
            $user,
            "Zostałeś przypisany do dyżuru w dniu {$currentDuty->duty_date} o godzinie {$currentDuty->hour}:00"
        );

        return Redirect::route('home')
            ->with('success', 'Dyżur został utworzony');
    }

    /**
     * Remove the specified duty.
     */
    public function destroy(CurrentDuty $duty): RedirectResponse
    {
        if (!Auth::user()->is_admin) {
            return Redirect::route('duties.index')
                ->with('error', 'Brak uprawnień do usunięcia tego dyżuru');
        }

        $duty->delete();

        return Redirect::route('duties.index')
            ->with('success', 'Dyżur został usunięty');
    }
}
