<?php
namespace App\Services;

use App\Enums\DutyType;
use App\Models\CurrentDuty;
use App\Models\CurrentDutyUser;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DutiesService
{
    public static function getCurrentDutyMostDistantDate(): Carbon
    {
        return Carbon::createFromDate(DB::table('current_duties')->max('date'));
    }

    public static function suspend(User $user)
    {
        if (!$user->suspend_from) {
            return;
        }

        CurrentDutyUser::where('user_id', $user->id)
            ->whereHas('currentDuty', function ($query) use ($user) {
                $query->where('date', '>=', $user->suspend_from);
                
                if ($user->suspend_to) {
                    $query->where('date', '<=', $user->suspend_to);
                }
            })
        ->delete();
    }

    public static function removeSuspention(User $user)
    {
        CurrentDutyUser::withTrashed()
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->whereHas('currentDuty', function ($query) use ($user) {
                $query->where('date', '>=', Carbon::now());
                
                if ($user->suspend_to) {
                    $query->where('date', '<=', $user->suspend_to);
                }
            })
            ->restore();
    }


    public static function addUserDuties(User $user, DutyPattern $dutyPattern)
    {
        $targetDayIndex = DateHelper::weekDayOffset($dutyPattern->day);

        if (! $targetDayIndex) {
            return; // Nieznany dzień tygodnia
        }

        // 1. Znajdujemy wszystkie pasujące terminy w current_duties
        // Filtrujemy po godzinie, dacie startowej oraz dniu tygodnia
        $duties = CurrentDuty::where('hour', $dutyPattern->hour)
            ->where('date', '>=', $dutyPattern->start_date)
            ->whereRaw('DAYOFWEEK(date) = ?', [$targetDayIndex])
            ->get();

        $inserts = [];

        foreach ($duties as $duty) {
            // 2. Uwzględniamy repeat_interval (np. co 3 tygodnie)
            if ($dutyPattern->repeat_interval > 1) {
                $start   = Carbon::parse($dutyPattern->start_date);
                $current = Carbon::parse($duty->date);

                // Obliczamy różnicę w tygodniach między datą startu a danym dyżurem
                $diffInWeeks = $start->diffInWeeks($current);

                // Jeśli różnica nie jest wielokrotnością interwału, pomijamy ten termin
                if ($diffInWeeks % $dutyPattern->repeat_interval !== 0) {
                    continue;
                }
            }

            // 3. Wstawiamy dane do current_duties_users
            // Korzystamy z duty_type z wzorca ('adoracja' lub 'rezerwa')
            $inserts[] = [
                'current_duty_id' => $duty->id,
                'user_id'         => $user->id,
                'duty_type'       => $dutyPattern->duty_type,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        if (! empty($inserts)) {
            CurrentDutyUser::insert($inserts);
        }

    }

    public static function removeUserDuties(DutyPattern $dutyPattern, User $user)
    {
        $targetDayIndex = DateHelper::weekDayOffset($dutyPattern->day);

        if (! $targetDayIndex) {
            return;
        }

        // 1. Znajdujemy potencjalne terminy w current_duties, które pasują do wzorca
        $duties = CurrentDuty::where('hour', $dutyPattern->hour)
            ->where('date', '>=', $dutyPattern->start_date)
            ->whereRaw('DAYOFWEEK(date) = ?', [$targetDayIndex])
            ->get();

        $dutyIdsToRemoval = [];

        foreach ($duties as $duty) {
            // 2. Uwzględniamy repeat_interval (identyczna logika jak przy dodawaniu)
            if ($dutyPattern->repeat_interval > 1) {
                $start   = Carbon::parse($dutyPattern->start_date);
                $current = Carbon::parse($duty->date);

                $diffInWeeks = $start->diffInWeeks($current);

                if ($diffInWeeks % $dutyPattern->repeat_interval !== 0) {
                    continue;
                }
            }

            $dutyIdsToRemoval[] = $duty->id;
        }

        // 3. Usuwamy wpisy z current_duties_users (Soft Delete)
        if (! empty($dutyIdsToRemoval)) {
            CurrentDutyUser::where('user_id', $user->id)
                ->where('duty_type', $dutyPattern->duty_type) // usuwamy tylko ten typ (adoracja/rezerwa), który był we wzorcu
                ->whereIn('current_duty_id', $dutyIdsToRemoval)
                ->delete();
        }
    }

    public static function updateUserDuties(User $user)
    {
        $startDate = Carbon::today();

        // 1. Usuwamy tylko automatyczne dyżury (changed_by = -1) od dzisiaj wzwyż
        CurrentDutyUser::where('user_id', $user->id)
            ->where('changed_by', -1) // Kluczowe: usuwamy tylko systemowe, nie ręczne
            ->whereHas('currentDuty', function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate->toDateString());
            })
            ->delete();

        // 2. Pobieramy wzorce (deklaracje) użytkownika
        $patterns = DutyPattern::where('user_id', $user->id)->get();

        if ($patterns->isEmpty()) {
            return;
        }

        // 3. Pobieramy sloty w systemie, które pasują do godzin we wzorcach (optymalizacja)
        $patternHours = $patterns->pluck('hour')->unique();

        $availableDuties = CurrentDuty::where('date', '>=', $startDate->toDateString())
            ->whereIn('hour', $patternHours)
            ->where('inactive', 0) // Pomijamy sloty wyłączone z użytku
            ->orderBy('date')
            ->get();

        $inserts = [];

        // 4. Iterujemy po dostępnych terminach
        foreach ($availableDuties as $duty) {
            $date = Carbon::parse($duty->date);

            // SPRAWDZENIE ZAWIESZENIA: na podstawie suspend_from i suspend_to z tabeli users
            if ($user->suspend_from && $user->suspend_to) {
                $from = Carbon::parse($user->suspend_from)->startOfDay();
                $to   = Carbon::parse($user->suspend_to)->endOfDay();

                if ($date->between($from, $to)) {
                    continue; // Użytkownik ma zawieszenie w tym dniu
                }
            }

            // Nazwa dnia tygodnia pasująca do nazw w bazie (np. "Wtorek")
            $dayName = mb_convert_case($date->translatedFormat('l'), MB_CASE_TITLE, "UTF-8");

            // Szukamy deklaracji pasującej do dnia tygodnia i godziny
            $matchingPatterns = $patterns->where('day', $dayName)
                ->where('hour', $duty->hour);

            foreach ($matchingPatterns as $pattern) {
                // Sprawdzenie daty rozpoczęcia wzorca
                if ($pattern->start_date && $date->lt(Carbon::parse($pattern->start_date))) {
                    continue;
                }

                // Sprawdzenie interwału tygodniowego (np. co 2 tygodnie)
                $isCorrectWeek = true;
                if ($pattern->repeat_interval > 1) {
                    // Obliczamy różnicę tygodni od daty startu (lub od początku tygodnia utworzenia)
                    $start       = Carbon::parse($pattern->start_date ?? $pattern->created_at)->startOfWeek();
                    $diffInWeeks = $start->diffInWeeks($date->copy()->startOfWeek());

                    if ($diffInWeeks % $pattern->repeat_interval !== 0) {
                        $isCorrectWeek = false;
                    }
                }

                if ($isCorrectWeek) {
                    $inserts[] = [
                        'user_id'         => $user->id,
                        'current_duty_id' => $duty->id,
                        'duty_type'       => $pattern->duty_type,
                        'changed_by'      => -1, // Oznaczenie systemowe
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }

        // 5. Masowe wstawianie dla wysokiej wydajności
        if (! empty($inserts)) {
            CurrentDutyUser::insert($inserts);
        }
    }

    public static function getCurrentDuties(Carbon $startDate)
    {
        $currentDuties = CurrentDuty::where('date', '>=', $startDate->format('Y-m-d'))->get();
        $ret           = [];
        foreach ($currentDuties as $duty) {
            $date = Carbon::create($duty->date)->format('Y-m-d');

            if (! isset($ret[$date])) {
                $ret[$date] = [];
            }
            $ret[$date][$duty->hour] = $duty;
        }
        return $ret;
    }

    public static function generateCurrentDuties(Collection $users, int $changedBy, $startDate = null, $noOfWeeks = 4)
    {
        $dateInserting = ($startDate ?: Carbon::now())->copy();

        // Obliczamy całkowitą liczbę dni do wygenerowania
        $totalDays = $noOfWeeks * 7;

        for ($i = 0; $i < $totalDays; $i++) {

            $rawDay  = $dateInserting->translatedFormat('l');
            $weekDay = mb_convert_case($rawDay, MB_CASE_TITLE, "UTF-8");

            foreach (Helper::DAY_HOURS as $hour) {
                $currentDuty = CurrentDuty::firstOrCreate([
                    'hour' => $hour,
                    'date' => $dateInserting->toDateString(),
                ]);

                // PRZEKAZUJEMY $dateInserting zamiast stałego $startDate
                $usersForTimeFrame = DutyPattern::getUsersForTimeFrame($dateInserting, $weekDay, $hour, $users);

                foreach ($usersForTimeFrame as $userDuty) {
                    $currentDuty->users()->attach($userDuty['user_id'], [
                        'duty_type'  => $userDuty['duty_type'],
                        'changed_by' => $changedBy,
                    ]);
                }
            }
            $dateInserting->addDay();
        }

    }

    public static function applySuspension(User $user, ?Carbon $suspendFrom, ?Carbon $suspendTo): void
    {
        CurrentDutyUser::where('user_id', $user->id)
            ->whereHas('currentDuty', function ($query) use ($suspendFrom, $suspendTo) {
                $query->where('date', '>=', $suspendFrom->toDateString());

                if ($suspendTo) {
                    $query->where('date', '<=', $suspendTo->toDateString());
                }
            })
            ->update(['duty_type' => DutyType::SUSPEND]);
    }



}
