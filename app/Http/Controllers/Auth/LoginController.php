<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ProvidersRouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use App\Http\Controllers\Auth\AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = ProvidersRouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm(): View
    {
        return ViewFacade::make('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Authentication passed...
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->redirectTo('/');
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return array<string, string>
     */
    protected function credentials(Request $request): array
    {
        return array_merge(
            $request->only($this->username(), 'password'),
            ['email_verified_at' => null]
        );
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->is_confirmed) {
            auth()->logout();
            return response()->json([
                'message' => 'Proszę potwierdzić adres email przed zalogowaniem.'
            ], 401);
        }
    }
}
