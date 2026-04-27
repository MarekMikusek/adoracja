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

    public static function updateUserDuties(User $user)
    {
        $startDate = Carbon::today();

        // 1. Usuwamy stare automatyczne dyżury (oznaczone jako changed_by = -1)
        // Dzięki whereHas usuwamy tylko te powiązane z datą przyszłą
        CurrentDutyUser::where('user_id', $user->id)
            ->where('changed_by', -1)
            ->whereHas('currentDuty', function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate->toDateString());
            })
            ->delete();

        // 2. Pobieramy wzorce użytkownika
        $patterns = DutyPattern::where('user_id', $user->id)->get();

        if ($patterns->isEmpty()) {
            return;
        }

        // 3. Pobieramy wszystkie wygenerowane sloty w systemie od dzisiaj
        $availableDuties = CurrentDuty::where('date', '>=', $startDate->toDateString())
            ->orderBy('date')
            ->get();

        $inserts = [];

        // 4. Iterujemy po istniejących terminach w kalendarzu
        foreach ($availableDuties as $duty) {
            $date = Carbon::parse($duty->date);

            // Sprawdzamy, czy użytkownik nie ma zawieszenia w tym konkretnym dniu
            if ($user->isSuspended($date)) {
                continue;
            }

            // Pobieramy nazwę dnia tygodnia (upewnij się, że format odpowiada temu w bazie, np. "Monday")
            // Jeśli w bazie masz polskie nazwy z wielkiej litery, użyj: mb_convert_case($date->translatedFormat('l'), MB_CASE_TITLE, "UTF-8")
            $rawName = $date->format('l');
            $dayName = mb_convert_case($date->translatedFormat('l'), MB_CASE_TITLE, "UTF-8");

            // Szukamy wzorca pasującego do dnia tygodnia i godziny
            $matchingPatterns = $patterns->where('day', $dayName)
                ->where('hour', $duty->hour);

            foreach ($matchingPatterns as $pattern) {
                // Sprawdzamy interwał tygodniowy (np. co 2 tygodnie)
                if ($pattern->isDutyInWeek($date)) {
                    $inserts[] = [
                        'user_id'         => $user->id,
                        'current_duty_id' => $duty->id,
                        'duty_type'       => $pattern->duty_type,
                        'changed_by'      => -1, // Oznaczamy jako systemowy
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }

        // 5. Masowe wstawianie dla wydajności (zamiast pojedynczych zapytań w pętli)
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
