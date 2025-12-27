<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display settings page.
     */
    public function index(): View
    {
        $settings = Setting::query()
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        return ViewFacade::make('settings.index', [
            'settings' => $settings,
            'notificationTypes' => [
                'email' => 'Email',
                'sms' => 'SMS',
                'both' => 'Email i SMS'
            ],
            'dutyTimeframes' => [
                1 => '1 tydzień',
                2 => '2 tygodnie',
                4 => '4 tygodnie'
            ]
        ]);
    }

    /**
     * Update application settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'duty_timeframe' => ['required', 'integer', 'in:1,2,4'],
            'default_notification_type' => ['required', 'in:email,sms,both'],
            'notification_advance_hours' => ['required', 'integer', 'min:1', 'max:72'],
            'min_duties_per_month' => ['required', 'integer', 'min:0'],
            'max_duties_per_month' => ['required', 'integer', 'min:1'],
            'duty_points_value' => ['required', 'numeric', 'min:0.1'],
            'enable_auto_assignment' => ['required', 'boolean'],
            'enable_duty_trading' => ['required', 'boolean']
        ]);

        foreach ($validated as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );

            // Clear cached setting
            Cache::forget("setting.{$key}");
        }

        return Redirect::route('settings.index')
            ->with('success', 'Ustawienia zostały zaktualizowane');
    }

    /**
     * Reset settings to default values.
     */
    public function reset(): RedirectResponse
    {
        $defaults = [
            'duty_timeframe' => 2,
            'default_notification_type' => 'email',
            'notification_advance_hours' => 24,
            'min_duties_per_month' => 2,
            'max_duties_per_month' => 8,
            'duty_points_value' => 1.0,
            'enable_auto_assignment' => true,
            'enable_duty_trading' => true
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );

            Cache::forget("setting.{$key}");
        }

        return Redirect::route('settings.index')
            ->with('success', 'Ustawienia zostały przywrócone do wartości domyślnych');
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): RedirectResponse
    {
        Cache::flush();

        return Redirect::route('settings.index')
            ->with('success', 'Cache aplikacji został wyczyszczony');
    }
} 