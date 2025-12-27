<?php

namespace App\Http\Controllers;

use App\Models\CurrentDuty;
use App\Models\DutyTrade;
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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class DutyTradeController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display duty trades.
     */
    public function index(): View
    {
        $trades = DutyTrade::query()
            ->with(['duty', 'requester', 'accepter'])
            ->where(function ($query) {
                $query->where('requester_id', Auth::id())
                    ->orWhere('duty_id', function ($subquery) {
                        $subquery->select('id')
                            ->from('current_duties')
                            ->where('user_id', Auth::id());
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ViewFacade::make('duty-trades.index', [
            'trades' => $trades
        ]);
    }

    /**
     * Store a new duty trade request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'duty_id' => ['required', 'exists:current_duties,id'],
            'proposed_date' => ['required', 'date', 'after:today'],
            'proposed_hour' => ['required', 'integer', 'between:0,23']
        ]);

        $duty = CurrentDuty::findOrFail($validated['duty_id']);

        if ($duty->user_id === Auth::id()) {
            return Redirect::back()
                ->with('error', 'Nie możesz wymienić dyżuru sam ze sobą');
        }

        DB::transaction(function () use ($validated, $duty) {
            $trade = DutyTrade::create([
                'duty_id' => $duty->id,
                'requester_id' => Auth::id(),
                'proposed_date' => $validated['proposed_date'],
                'proposed_hour' => $validated['proposed_hour']
            ]);

            // Send notification to duty owner
            $this->notificationService->sendNotification(
                $duty->user,
                "Otrzymałeś propozycję wymiany dyżuru z dnia {$duty->duty_date} godz. {$duty->hour}:00"
            );
        });

        return Redirect::route('duty-trades.index')
            ->with('success', 'Propozycja wymiany została wysłana');
    }

    /**
     * Accept duty trade request.
     */
    public function accept(DutyTrade $trade): RedirectResponse
    {
        if ($trade->duty->user_id !== Auth::id()) {
            return Redirect::route('duty-trades.index')
                ->with('error', 'Nie możesz zaakceptować tej wymiany');
        }

        if ($trade->status !== 'pending') {
            return Redirect::route('duty-trades.index')
                ->with('error', 'Ta wymiana została już rozpatrzona');
        }

        DB::transaction(function () use ($trade) {
            // Create new duty for requester
            CurrentDuty::create([
                'user_id' => $trade->requester_id,
                'duty_date' => $trade->duty->duty_date,
                'hour' => $trade->duty->hour
            ]);

            // Create new duty for accepter
            CurrentDuty::create([
                'user_id' => Auth::id(),
                'duty_date' => $trade->proposed_date,
                'hour' => $trade->proposed_hour
            ]);

            // Delete original duty
            $trade->duty->delete();

            // Update trade status
            $trade->update([
                'status' => 'accepted',
                'accepter_id' => Auth::id()
            ]);

            // Send notification to requester
            $this->notificationService->sendNotification(
                $trade->requester,
                'Twoja propozycja wymiany dyżuru została zaakceptowana'
            );
        });

        return Redirect::route('duty-trades.index')
            ->with('success', 'Wymiana dyżuru została zaakceptowana');
    }

    /**
     * Reject duty trade request.
     */
    public function reject(DutyTrade $trade): RedirectResponse
    {
        if ($trade->duty->user_id !== Auth::id()) {
            return Redirect::route('duty-trades.index')
                ->with('error', 'Nie możesz odrzucić tej wymiany');
        }

        if ($trade->status !== 'pending') {
            return Redirect::route('duty-trades.index')
                ->with('error', 'Ta wymiana została już rozpatrzona');
        }

        $trade->update([
            'status' => 'rejected',
            'accepter_id' => Auth::id()
        ]);

        // Send notification to requester
        $this->notificationService->sendNotification(
            $trade->requester,
            'Twoja propozycja wymiany dyżuru została odrzucona'
        );

        return Redirect::route('duty-trades.index')
            ->with('success', 'Wymiana dyżuru została odrzucona');
    }
} 