<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class ConfirmPasswordController extends Controller
{
    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the password confirmation view.
     */
    public function showConfirmForm(): View
    {
        return ViewFacade::make('auth.passwords.confirm');
    }

    /**
     * Confirm the given user's password.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $this->resetPasswordConfirmationTimeout($request);

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the password confirmation validation rules.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'password' => ['required', 'current_password:web'],
        ];
    }

    /**
     * Get the password confirmation validation error messages.
     *
     * @return array<string, string>
     */
    protected function validationErrorMessages(): array
    {
        return [
            'password.current_password' => __('The provided password does not match your current password.'),
        ];
    }
} 