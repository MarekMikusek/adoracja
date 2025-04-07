<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\SuspendDutyJob;
use App\Models\WaysOfContact;
use App\Services\DutiesService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View as FacadesView;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'waysOfContacts' => WaysOfContact::all()
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('home')->with('status', 'profile-updated');
    }

    public function editSuspend()
    {
        return FacadesView::make('profile.suspend', [
            'user' => Auth::user()
        ]);
    }

    public function saveSuspend(Request $request)
    {
        $user = Auth::user();
        $user->suspend_from = $request->input('suspend_from') ? new Carbon($request->input('suspend_from')) : null;
        $user->suspend_to = $request->input('suspend_to') ? new Carbon($request->input('suspend_to')) : null;

        $user->save();

        DutiesService::applySuspension($user);

        if($user->email){
            SuspendDutyJob::dispatch($user);
        }

        return response()->redirectTo('/');
    }



    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
