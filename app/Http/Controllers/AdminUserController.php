<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminDeleteUserRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Http\Requests\PatternStoreRequest;
use App\Http\Requests\VerifyUserRequest;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\DutiesService;
use App\Services\Helper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class AdminUserController extends Controller
{

public function __construct()
{

}
    public function index()
    {
        $users = User::orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return View::make('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function verifyUser(VerifyUserRequest $request): RedirectResponse
    {

        $user = User::find($request->validated()['user_id'])->update(['is_confirmed' => true]);

        return Redirect::route('admin.users')
            ->with('success', 'Email użytkownika został zweryfikowany');
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(User $user)
    {
        return View::make('admin.users.edit', ['user' => $user]);
    }

    public function update(AdminUserUpdateRequest $request, User $user): RedirectResponse
    {
        $validated                 = $request->validated();
        $validated['is_admin']     = (isset($validated['is_admin']) && $validated['is_admin'] == "on") ? true : false;
        $validated['suspend_from'] == $validated['suspend_from'] ?? null;
        $validated['suspend_to'] == $validated['suspend_to'] ?? null;

        $user->update($validated);

        return Redirect::route('admin.users')
            ->with('success', 'Dane użytkownika zostały zaktualizowane');
    }

    public function destroy(AdminDeleteUserRequest $request)
    {
        $user = User::find($request->validated()['user']);
        $user->delete();
        return redirect()->route('admin.users');
    }


    public function showUserDuties(User $user)
    {
        $patterns = DutyPattern::query()
        ->select(['hour', 'day', 'id','duty_type', 'repeat_interval'])
        ->where('user_id', $user->id)
        ->get()
        ->groupBy('duty_type');

        $weekDays = Helper::getWeekDays();
        $hours = Helper::getHours();
        $intervals = Helper::getIntervals();

        return view('admin.users.user-patterns', compact('user', 'patterns', 'weekDays', 'hours', 'intervals'));
    }

    public function userPatternsStore(User $user, PatternStoreRequest $pattern)
    {
        $pattern = $pattern->validated();
        $duty = DutyPattern::query()->create([
            'user_id'         => $user->id,
            'day'             => $pattern['day'],
            'hour'            => $pattern['hour'],
            'duty_type'       => $pattern['duty_type'],
            'repeat_interval' => $pattern['repeat_interval'],
        ]);
        DutiesService::updateUserDuties($user);
        return Redirect::route('admin.users.patterns', ['user' => $user->id]);
    }

}
