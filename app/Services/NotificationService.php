<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Notifications\DutyNotification;
use GuzzleHttp\Client;

class NotificationService
{
    private $smsApiKey;
    private $smsApiUrl;

    public function __construct()
    {
        $this->smsApiKey = config('services.sms.api_key');
        $this->smsApiUrl = config('services.sms.api_url');
    }

    /**
     * Send notification to a user.
     *
     * @param  User  $user
     * @param  string  $message
     * @return void
     */
    public function sendNotification(User $user, string $message): void
    {
        try {
            $user->notify(new DutyNotification($message));
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications to multiple users.
     *
     * @param  \Illuminate\Support\Collection<User>  $users
     * @param  string  $message
     * @return void
     */
    public function sendBulkNotifications($users, string $message): void
    {
        foreach ($users as $user) {
            $this->sendNotification($user, $message);
        }
    }

    private function sendEmail(User $user, string $message)
    {
        try {
            Mail::to($user->email)
                ->send(new DutyNotification($message));
            return true;
        } catch (\Exception $e) {
            \Log::error('Email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendSMS(User $user, string $message)
    {
        if (!$user->phone_number) {
            return false;
        }

        try {
            $client = new Client();
            $response = $client->post($this->smsApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->smsApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'phone' => $user->phone_number,
                    'message' => $message,
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            \Log::error('SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }
} 