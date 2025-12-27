<?php

namespace App\Http\Controllers;

use App\Http\Requests\MainCoordinatorEmailRequest;
use App\Mail\EmailFromWeb;
use App\Mail\TestEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View as ViewFacade;

class MainCoordinatorsController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $mainCoordinators = DB::table('main_coordinators as mc')
        ->join('users as u', 'u.id', 'mc.id')
        ->select('mc.id', 'u.first_name', 'u.last_name', 'u.phone_number')
        ->get();

        return ViewFacade::make('main-coordinators', ['coordinators' => $mainCoordinators]);
    }

        public function sendEmail(MainCoordinatorEmailRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $coordinator = User::findOrFail($validated['coordinator_id']);

            Mail::to($coordinator->email)->send(new EmailFromWeb($validated['message']));
            // $this->notificationService->sendNotification($coordinator, $validated['message']);

            return response()->json([
                'success' => true,
                'message' => 'Wiadomość została wysłana do koordynatora.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending coordinator email', [
                'coordinator_id' => $validated['coordinator_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Wystąpił błąd podczas wysyłania wiadomości.',
            ], 500);
        }
    }
}
