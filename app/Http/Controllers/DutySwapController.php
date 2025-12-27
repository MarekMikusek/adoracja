<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutySwap;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DutySwapController extends Controller
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
     * Display duty swap requests.
     */
    public function index(): View
    {
        $swaps = DutySwap::query()
            ->with(['originalDuty.user', 'targetDuty.user'])
            ->where(function ($query) {
                $query->whereHas('originalDuty', function ($q) {
                    $q->where('user_id', Auth::id());
                })->orWhereHas('targetDuty', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ViewFacade::make('duty-swaps.index', [
            'swaps' => $swaps
        ]);
    }

    /**
     * Store a new duty swap request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'original_duty_id' => ['required', 'exists:current_duties,id'],
            'target_duty_id' => ['required', 'exists:current_duties,id', 'different:original_duty_id']
        ]);

        $originalDuty = CurrentDuty::findOrFail($validated['original_duty_id']);
        $targetDuty = CurrentDuty::findOrFail($validated['target_duty_id']);

        if ($originalDuty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz zamienić cudzego dyżuru');
        }

        if ($targetDuty->user_id === Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz zamienić dyżuru sam ze sobą');
        }

        DB::transaction(function () use ($originalDuty, $targetDuty) {
            $swap = DutySwap::create([
                'original_duty_id' => $originalDuty->id,
                'target_duty_id' => $targetDuty->id,
                'status' => 'pending'
            ]);

            // Send notification to target user
            $this->notificationService->sendNotification(
                $targetDuty->user,
                "Otrzymałeś propozycję zamiany dyżuru z dnia {$targetDuty->duty_date} godz. {$targetDuty->hour}:00"
            );
        });

        return Redirect::route('duty-swaps.index')
            ->with('success', 'Propozycja zamiany została wysłana');
    }

    /**
     * Accept duty swap request.
     */
    public function accept(DutySwap $swap): RedirectResponse
    {
        if ($swap->targetDuty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz zaakceptować tej zamiany');
        }

        if ($swap->status !== 'pending') {
            return Redirect::back()
                ->with('error', 'Ta zamiana została już rozpatrzona');
        }

        DB::transaction(function () use ($swap) {
            // Swap user IDs
            $originalUserId = $swap->originalDuty->user_id;
            $targetUserId = $swap->targetDuty->user_id;

            $swap->originalDuty->update(['user_id' => $targetUserId]);
            $swap->targetDuty->update(['user_id' => $originalUserId]);

            $swap->update([
                'status' => 'accepted',
                'accepted_at' => Carbon::now()
            ]);

            // Send notification to original user
            $this->notificationService->sendNotification(
                $swap->originalDuty->user,
                'Twoja propozycja zamiany dyżuru została zaakceptowana'
            );
        });

        return Redirect::route('duty-swaps.index')
            ->with('success', 'Zamiana dyżuru została zaakceptowana');
    }

    /**
     * Reject duty swap request.
     */
    public function reject(DutySwap $swap): RedirectResponse
    {
        if ($swap->targetDuty->user_id !== Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz odrzucić tej zamiany');
        }

        if ($swap->status !== 'pending') {
            return Redirect::back()
                ->with('error', 'Ta zamiana została już rozpatrzona');
        }

        $swap->update([
            'status' => 'rejected',
            'rejected_at' => Carbon::now()
        ]);

        // Send notification to original user
        $this->notificationService->sendNotification(
            $swap->originalDuty->user,
            'Twoja propozycja zamiany dyżuru została odrzucona'
        );

        return Redirect::route('duty-swaps.index')
            ->with('success', 'Zamiana dyżuru została odrzucona');
    }
} 