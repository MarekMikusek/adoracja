<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRegisteredRequest;
use App\Http\Requests\CheckEmailRequest;
use App\Models\User;
use App\Jobs\AccountRegistered;
use App\Jobs\AccountRegisteredJob;
use App\Models\WaysOfContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', ['waysOfContact'=>WaysOfContact::all()]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(AccountRegisteredRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'ways_of_contacts_id' => $data['ways_of_contacts_id'],
            'rodo_clause' => 1,
            'added_by' => 0
        ]);

        $user['added_by'] = $user->id;
        $user->save();

        Auth::loginUsingId($user->id);

        AccountRegisteredJob::dispatch($user);

        return response()->redirectTo('/');
    }


    public function checkEmail(CheckEmailRequest $request)
    {
        $user = User::where(['email' => $request->validated()['email']])->first();

        return empty($user) ? 'free' : 'taken';
    }
}
