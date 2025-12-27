<?php
namespace App\Http\Controllers;

use App\Http\Requests\AdminDeleteUserRequest;
use App\Http\Requests\AdminUserStoreRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Http\Requests\PatternStoreRequest;
use App\Http\Requests\RemoveAdminCurrentDutyRequest;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\VerifyUserRequest;
use App\Models\CurrentDuty;
use App\Models\CurrentDutyUser;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\DateHelper;
use App\Services\DutiesService;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
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

    public function removeDuty(RemoveAdminCurrentDutyRequest $request)
    {
        $data = $request->validated();

        $currentDury = CurrentDuty::find($data['duty_id']);
        $currentDury->delete();

    }

    public function duties(User $user)
    {
        $duties = CurrentDutyUser::query()
            ->join('current_duties as cd', 'cd.id', 'current_duties_users.current_duty_id')
            ->join('users as u', 'u.id', 'current_duties_users.user_id')
            ->where('cd.date', '>=', Carbon::today()->subWeeks(2))
            ->where('user_id', $user->id)
            ->select(['current_duty_id', 'date', 'hour', 'duty_type', 'cd.inactive'])
            ->orderBy('duty_type')
            ->orderBy('date')
            ->orderBy('hour')
            ->get();

        foreach ($duties as $duty) {
            $duty['name_of_day'] = DateHelper::dayOfWeek($duty['date']);
            $duty['historical'] = $duty['date'] < Carbon::today();
        }

        $duties = $duties->groupBy('duty_type');
        $duties['adoracja'] = $duties['adoracja'] ?? [];
        $duties['lista_rezerwowa'] = $duties['rezerwa'] ?? [];

        unset($duties['rezerwa']);

        return View::make('admin.users.duties', [
            'duties' => $duties,
            'user' => $user
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


    public function store(AdminUserStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = (isset($validated['is_admin']) && $validated['is_admin']) === 'on' ? 1 : 0;
        $validated['added_by'] = Auth::id();
        $validated['rodo_clause'] = $validated['rodo_clause'] === 'on' ? 1 : 0;

        User::create($validated);

        return Redirect::route('admin.users')->with('success', 'User added successfully.');
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
        $validated             = $request->validated();
        $validated['is_admin'] = (isset($validated['is_admin']) && $validated['is_admin'] == "on") ? true : false;
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
            ->select(['hour', 'day', 'id', 'duty_type', 'repeat_interval'])
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('duty_type');

        $weekDays  = Helper::getWeekDays();
        $hours     = Helper::getHours();
        $intervals = Helper::getIntervals();

        return view('admin.users.user-patterns', compact('user', 'patterns', 'weekDays', 'hours', 'intervals'));
    }

    public function userPatternsStore(User $user, PatternStoreRequest $pattern)
    {
        $pattern = $pattern->validated();
        $duty    = DutyPattern::query()->create([
            'user_id'         => $user->id,
            'day'             => $pattern['day'],
            'hour'            => $pattern['hour'],
            'duty_type'       => $pattern['duty_type'],
            'repeat_interval' => $pattern['repeat_interval'],
        ]);
        DutiesService::updateUserDuties($user);
        return Redirect::route('admin.users.patterns', ['user' => $user->id]);
    }

    public function searchUser(SearchUserRequest $request)
    {
        dd($request->validated());
    }

}
