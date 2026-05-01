<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DateHelper;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;

class HomeController extends Controller
{
    public const MY_DUTY_COLOUR  = '#A7C7E7';
    public const REZERWA_COLOUR  = '#FFE440';
    public const NO_DUTY_COLOUR  = '#FFFFFF';
    public const HAS_DUTY_COLOUR = '#98FB98';

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user   = Auth::user();
        $userId = $user ? $user->id : null;

        $upcomingDuties = DB::table('current_duties as cd')
            ->select([
                'cd.id as duty_id',
                'cd.date',
                'cd.hour',
                'cd.inactive',
                'cdu.user_id',
                'cdu.duty_type',
            ])
            ->leftJoin('current_duties_users as cdu', function ($join) {
                $join->on('cdu.current_duty_id', '=', 'cd.id')
                    ->whereNull('cdu.deleted_at');
            })
            ->where('cd.date', '>=', Carbon::today()->toDateString())
            ->orderBy('cd.date')
            ->orderBy('cd.hour')
            ->get();

        $duties = [];

        foreach ($upcomingDuties as $duty) {
            $dateFormatted = Carbon::parse($duty->date)->isoFormat('D MMMM');

            // 2. Inicjalizacja dnia, jeśli jeszcze nie istnieje w tablicy
            if (! isset($duties[$dateFormatted])) {
                $duties[$dateFormatted] = [
                    'dayName'       => DateHelper::dayOfWeek($duty->date),
                    'dateFormatted' => Carbon::parse($duty->date)->format('d.m'),
                    'timeFrames'    => [],
                ];

                // Wstępne wypełnienie wszystkich godzin z Helper::DAY_HOURS
                foreach (Helper::DAY_HOURS as $h) {
                    $duties[$dateFormatted]['timeFrames'][$h] = [
                        'hour'         => $h, // POPRAWKA: przypisujemy $h, a nie $duty->hour
                        'dutyId'       => null,
                        'inactive'     => 0,
                        'adoracja'     => 0,
                        'userDutyType' => '',
                    ];
                }
            }

            // 3. Wypełnianie danych dla konkretnego slotu
            $hourRef = &$duties[$dateFormatted]['timeFrames'][$duty->hour];

            // Przypisujemy ID dyżuru (potrzebne do linków/akcji)
            $hourRef['dutyId'] = $duty->duty_id;

            if ($duty->inactive == 1) {
                $hourRef['inactive'] = 1;
            }

            // Sprawdzamy czy to dyżur zalogowanego użytkownika
            if ($userId && $duty->user_id == $userId) {
                // Jeśli użytkownik ma tu jakikolwiek wpis (nawet jeśli to nie 'adoracja'), oznaczamy typ
                $hourRef['userDutyType'] = $duty->duty_type;
            }

            // Licznik osób zapisanych na adorację (może być ich kilku na jedną godzinę)
            if ($duty->user_id && $duty->duty_type === 'adoracja') {
                $hourRef['adoracja']++;
            }
        }

        return ViewFacade::make('home', [
            'user'            => $user,
            'duties'          => $duties,
            'admins'          => collect(User::admins())->keyBy('id')->toArray(),
            'dayHours'        => Helper::DAY_HOURS,
            'myDutyColour'    => self::MY_DUTY_COLOUR,
            'myReserveColour' => self::REZERWA_COLOUR,
            'noDutyColour'    => self::NO_DUTY_COLOUR,
            'hasDutyColour'   => self::HAS_DUTY_COLOUR,
        ]);
    }

    public function rodo()
    {
        return ViewFacade::make('rodo');
    }

    public function mainCoordinator()
    {
        return ViewFacade::make('main-coordinator');
    }

}
