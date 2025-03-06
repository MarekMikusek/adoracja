<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;

class GoogleCalendarController extends Controller
{
    private $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->middleware('auth');
        $this->googleCalendarService = $googleCalendarService;
    }

    public function connect()
    {
        $client = $this->googleCalendarService->getClient();
        $authUrl = $client->createAuthUrl();
        return response()->json(['url' => $authUrl]);
    }

    public function callback(Request $request)
    {
        try {
            $client = $this->googleCalendarService->getClient();
            $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);
            
            auth()->user()->update([
                'google_token' => json_encode($accessToken)
            ]);

            return response()->json(['message' => 'Połączono z Google Calendar']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Błąd połączenia z Google Calendar'], 500);
        }
    }
} 