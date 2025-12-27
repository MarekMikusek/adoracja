<?php

namespace App\Http\Controllers;

use App\Models\DutyReward;
use App\Models\DutyRewardRedemption;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DutyRewardController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display available rewards.
     */
    public function index(): View
    {
        $rewards = DutyReward::query()
            ->where('is_active', true)
            ->orderBy('required_points')
            ->get();

        $userPoints = Auth::user()->total_points;
        $redeemedRewards = Auth::user()
            ->rewardRedemptions()
            ->with('reward')
            ->orderBy('created_at', 'desc')
            ->get();

        return ViewFacade::make('duty-rewards.index', [
            'rewards' => $rewards,
            'userPoints' => $userPoints,
            'redeemedRewards' => $redeemedRewards
        ]);
    }

    /**
     * Store a new reward.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::user()->is_admin) {
            return Redirect::back()
                ->with('error', 'Brak uprawnień do dodawania nagród');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'required_points' => ['required', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:2048'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after:today']
        ]);

        DB::transaction(function () use ($validated, $request) {
            $reward = DutyReward::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'required_points' => $validated['required_points'],
                'quantity' => $validated['quantity'],
                'expiry_date' => $validated['expiry_date'],
                'is_active' => true
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('rewards');
                $reward->update(['image_path' => $path]);
            }
        });

        return Redirect::route('duty-rewards.index')
            ->with('success', 'Nagroda została dodana');
    }

    /**
     * Update the specified reward.
     */
    public function update(Request $request, DutyReward $reward): RedirectResponse
    {
        if (!Auth::user()->is_admin) {
            return Redirect::back()
                ->with('error', 'Brak uprawnień do edycji nagród');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'required_points' => ['required', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:2048'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'is_active' => ['required', 'boolean']
        ]);

        DB::transaction(function () use ($validated, $request, $reward) {
            $reward->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'required_points' => $validated['required_points'],
                'quantity' => $validated['quantity'],
                'expiry_date' => $validated['expiry_date'],
                'is_active' => $validated['is_active']
            ]);

            if ($request->hasFile('image')) {
                if ($reward->image_path) {
                    Storage::delete($reward->image_path);
                }
                $path = $request->file('image')->store('rewards');
                $reward->update(['image_path' => $path]);
            }
        });

        return Redirect::route('duty-rewards.index')
            ->with('success', 'Nagroda została zaktualizowana');
    }

    /**
     * Redeem a reward.
     */
    public function redeem(DutyReward $reward): RedirectResponse
    {
        if (!$reward->is_active) {
            return Redirect::back()
                ->with('error', 'Ta nagroda nie jest już dostępna');
        }

        if ($reward->quantity !== null && $reward->quantity <= 0) {
            return Redirect::back()
                ->with('error', 'Ta nagroda została już wyczerpana');
        }

        if ($reward->expiry_date && Carbon::parse($reward->expiry_date)->isPast()) {
            return Redirect::back()
                ->with('error', 'Termin ważności tej nagrody upłynął');
        }

        $user = Auth::user();
        if ($user->total_points < $reward->required_points) {
            return Redirect::back()
                ->with('error', 'Nie masz wystarczającej liczby punktów');
        }

        DB::transaction(function () use ($reward, $user) {
            DutyRewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->required_points
            ]);

            $user->decrement('total_points', $reward->required_points);

            if ($reward->quantity !== null) {
                $reward->decrement('quantity');
            }

            // Send notification to admin
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $this->notificationService->sendNotification(
                    $admin,
                    "Użytkownik {$user->full_name} wymienił punkty na nagrodę: {$reward->name}"
                );
            }
        });

        return Redirect::route('duty-rewards.index')
            ->with('success', 'Nagroda została pomyślnie odebrana');
    }
} 