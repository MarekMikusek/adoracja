<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\DutySuspendedMail;
use App\Mail\DutySuspentionRemovedMail;
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
        /**@var \App\Models\User $user*/
        $user = auth()->user();
        $suspendFrom = $request->input('suspend_from') ? Carbon::parse($request->input('suspend_from'))->startOfDay() : null;
        $user->suspend_from = $suspendFrom?  $suspendFrom->startOfDay() : null;

        $suspendTo = $request->input('suspend_to') ? Carbon::parse($request->input('suspend_to'))->endOfDay() : null;
        $user->suspend_to = $suspendTo ?  $suspendTo->endOfDay() : null;

        $user->save();

        if($suspendFrom){
            DutiesService::suspend($user);
            if($user->hasRealEmail()){
                Mail::to($user->email)->send(new DutySuspendedMail($user));
            }
        } else {
            DutiesService::removeSuspention($user);
            if($user->hasRealEmail()){
                Mail::to($user->email)->send(new DutySuspentionRemovedMail($user));
            }
        }

        return response()->redirectTo('/');
    }

    public function removeAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
