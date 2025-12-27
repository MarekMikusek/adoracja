<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View; // Zmieniamy z Inertia na View

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view (Formularz 1).
     */
    public function create(): View // Zmieniony typ zwracany na View
    {
        return view('auth.forgot-password', [ // Używamy funkcji view()
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request (POST, Wysyłanie emaila).
     */
    public function store(Request $request): RedirectResponse
    {
        // ... (Reszta kodu jest poprawna, bo jest standardowa dla Laravela)
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
