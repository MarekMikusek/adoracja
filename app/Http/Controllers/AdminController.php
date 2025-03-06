<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DutyPattern;
use App\Models\CurrentDuty;
use App\Models\ReservePattern;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use WeekDays;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display user management page.
     */
    public function users(): View
    {
        $users = User::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return ViewFacade::make('admin.users.index', [
            'users' => $users
        ]);
    }

    /**
     * Update user details.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'notification_preference' => ['required', 'in:email,sms'],
            'is_admin' => ['sometimes', 'boolean']
        ]);

        $user->update($validated);

        return Redirect::route('admin.users')
            ->with('success', 'Dane użytkownika zostały zaktualizowane');
    }

    /**
     * Verify user's email manually.
     */
    public function verifyUser(User $user): RedirectResponse
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return Redirect::route('admin.users')
            ->with('success', 'Email użytkownika został zweryfikowany');
    }

    /**
     * Display admin hours management page.
     */
    public function hours(): View
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->with('dutyPatterns')
            ->get();

        $adminHours = $admins->mapWithKeys(function ($admin) {
            return [$admin->id => $admin->dutyPatterns];
        });

        return ViewFacade::make('admin.hours.index', [
            'admins' => $admins,
            'adminHours' => $adminHours,
            'weekDays' => WeekDays::DAYS
        ]);
    }

    /**
     * Store admin duty hours.
     */
    public function storeHours(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
            'days' => ['required', 'array'],
            'days.*' => ['integer', 'between:0,6'],
            'hours' => ['required', 'array'],
            'hours.*' => ['integer', 'between:0,23']
        ]);

        $admin = User::findOrFail($validated['admin_id']);

        // Clear existing patterns
        $admin->dutyPatterns()->delete();

        // Create new patterns
        foreach ($validated['days'] as $day) {
            foreach ($validated['hours'] as $hour) {
                $admin->dutyPatterns()->create([
                    'day_of_week' => $day,
                    'hour' => $hour,
                    'is_admin_duty' => true
                ]);
            }
        }

        return Redirect::route('admin.hours')
            ->with('success', 'Godziny dyżurów zostały zapisane');
    }

    public function getAdminHours()
    {
        $adminHours = DutyPattern::where('is_admin_duty', true)
            ->with('user')
            ->get()
            ->groupBy('user_id');

        return response()->json(['adminHours' => $adminHours]);
    }

    public function getDutyUsers($date, $hour)
    {
        $duties = CurrentDuty::where('duty_date', $date)
            ->where('hour', $hour)
            ->with('user')
            ->get();

        $reserves = ReservePattern::where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->where('hour', $hour)
            ->with('user')
            ->get();

        return response()->json([
            'duties' => $duties,
            'reserves' => $reserves
        ]);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message' => 'required|string'
        ]);

        // TODO: Implement notification sending

        return response()->json(['message' => 'Powiadomienia zostały wysłane']);
    }
}
