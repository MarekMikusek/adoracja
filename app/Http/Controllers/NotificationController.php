<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessagesRequest;
use App\Jobs\UserMessageJob;
use App\Mail\UserMessageMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's notifications.
     */
    public function index(): View
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(10);

        return ViewFacade::make('notifications.index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return Redirect::route('notifications.index')
            ->with('success', 'Powiadomienie oznaczono jako przeczytane');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()
            ->unreadNotifications
            ->markAsRead();

        return Redirect::route('notifications.index')
            ->with('success', 'Wszystkie powiadomienia oznaczono jako przeczytane');
    }

    public function sendMessages(SendMessagesRequest $request)
    {
        $data = $request->validated();

        $users = User::whereIn('id', json_decode($data['users_ids']))->get();

        $notSend = [];
        foreach($users as $user){
            if($user->email){
                \Log::info('sending' . $data['message']);
                UserMessageJob::dispatch($data['message'], $user);
            } else {
                $notSend[] = $user;
            }
        }
        return json_encode(['message' => 'messegages send', 'failed' => $notSend]);
    }
}