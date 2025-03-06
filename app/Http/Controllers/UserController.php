<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\NotificationService;

class UserController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display user profile.
     */
    public function profile(): View
    {
        return ViewFacade::make('users.profile', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'notification_preference' => ['required', 'in:email,sms']
        ]);

        $user->update($validated);

        if ($request->email !== $user->email) {
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
        }

        return Redirect::route('profile')
            ->with('success', 'Profil został zaktualizowany');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        return Redirect::route('profile')
            ->with('success', 'Hasło zostało zmienione');
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notification_preference' => ['required', 'in:email,sms']
        ]);

        Auth::user()->update($validated);

        // Send test notification
        $this->notificationService->sendNotification(
            Auth::user(),
            'To jest testowe powiadomienie potwierdzające zmianę ustawień.'
        );

        return Redirect::route('profile')
            ->with('success', 'Ustawienia powiadomień zostały zaktualizowane');
    }

    /**
     * Delete user account.
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        $user->delete();

        return Redirect::route('')
            ->with('success', 'Konto zostało usunięte');
    }
}
