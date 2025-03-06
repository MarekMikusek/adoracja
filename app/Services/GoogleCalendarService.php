<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use App\Models\User;
use Carbon\Carbon;

class GoogleCalendarService
{
    private $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
        $this->client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
    }

    public function addDutyEvent(User $user, $dutyDate, $hour)
    {
        try {
            if (!$user->google_token) {
                return false;
            }

            $this->client->setAccessToken($user->google_token);

            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                $user->update(['google_token' => $this->client->getAccessToken()]);
            }

            $service = new Google_Service_Calendar($this->client);

            $startTime = Carbon::parse($dutyDate)->setHour($hour);
            $endTime = $startTime->copy()->addHour();

            $event = new Google_Service_Calendar_Event([
                'summary' => 'Dyżur',
                'description' => 'Twój dyżur w systemie',
                'start' => ['dateTime' => $startTime->format('c')],
                'end' => ['dateTime' => $endTime->format('c')],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 60],
                        ['method' => 'popup', 'minutes' => 30],
                    ],
                ],
            ]);

            $service->events->insert('primary', $event);
            return true;
        } catch (\Exception $e) {
            \Log::error('Google Calendar integration failed: ' . $e->getMessage());
            return false;
        }
    }
} 