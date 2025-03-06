<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutyReminder;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DutyReminderController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display duty reminders.
     */
    public function index(): View
    {
        $reminders = DutyReminder::query()
            ->where('user_id', Auth::id())
            ->with('duty')
            ->orderBy('remind_at')
            ->paginate(20);

        return ViewFacade::make('duty-reminders.index', [
            'reminders' => $reminders
        ]);
    }

    /**
     * Store a new duty reminder.
     */
    public function store(Request $request, CurrentDuty $duty): RedirectResponse
    {
        if ($duty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz ustawić przypomnienia dla cudzego dyżuru');
        }

        $validated = $request->validate([
            'remind_at' => [
                'required', 
                'date', 
                'before:' . $duty->duty_date . ' ' . $duty->hour . ':00',
                'after:now'
            ],
            'message' => ['nullable', 'string', 'max:255']
        ]);

        DutyReminder::create([
            'user_id' => Auth::id(),
            'duty_id' => $duty->id,
            'remind_at' => $validated['remind_at'],
            'message' => $validated['message'] ?? 'Przypomnienie o dyżurze',
            'status' => 'pending'
        ]);

        return Redirect::route('duty-reminders.index')
            ->with('success', 'Przypomnienie zostało ustawione');
    }

    /**
     * Update the specified duty reminder.
     */
    public function update(Request $request, DutyReminder $reminder): RedirectResponse
    {
        if ($reminder->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz edytować cudzego przypomnienia');
        }

        $validated = $request->validate([
            'remind_at' => [
                'required', 
                'date', 
                'before:' . $reminder->duty->duty_date . ' ' . $reminder->duty->hour . ':00',
                'after:now'
            ],
            'message' => ['nullable', 'string', 'max:255']
        ]);

        $reminder->update([
            'remind_at' => $validated['remind_at'],
            'message' => $validated['message'] ?? $reminder->message
        ]);

        return Redirect::route('duty-reminders.index')
            ->with('success', 'Przypomnienie zostało zaktualizowane');
    }

    /**
     * Remove the specified duty reminder.
     */
    public function destroy(DutyReminder $reminder): RedirectResponse
    {
        if ($reminder->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz usunąć cudzego przypomnienia');
        }

        $reminder->delete();

        return Redirect::route('duty-reminders.index')
            ->with('success', 'Przypomnienie zostało usunięte');
    }

    /**
     * Send pending reminders.
     */
    public function sendPendingReminders(): void
    {
        $reminders = DutyReminder::query()
            ->where('status', 'pending')
            ->where('remind_at', '<=', Carbon::now())
            ->with(['user', 'duty'])
            ->get();

        foreach ($reminders as $reminder) {
            $this->notificationService->sendNotification(
                $reminder->user,
                $reminder->message . "\nDyżur: {$reminder->duty->duty_date} godz. {$reminder->duty->hour}:00"
            );

            $reminder->update(['status' => 'sent']);
        }
    }
} 