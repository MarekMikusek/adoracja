<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property string $day
 * @property int $hour
 * @property string $duty_type
 * @property int $repeat_interval
 * @property string|null $start_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereDutyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereRepeatInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DutyPattern whereUserId($value)
 * @mixin \Eloquent
 */
class DutyPattern extends Model
{
    protected $table    = 'duty_patterns';
    protected $fillable = [
        'user_id',
        'day',
        'hour',
        'start_date',
        'duty_type',
        'repeat_interval',
        'repeat_pattern',
        'added_by',
    ];

    public $patternKey = 'duty';

    protected $casts = [
        'is_admin_duty' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getUsersForTimeFrame(Carbon $currentDate, string $weekDay, int $hour, Collection $users)
    {
        $mappedUsers = $users->keyBy('id');

        // Pobieramy wzorce i filtrujemy je za pomocą metod kolekcji (reject)
        return self::where(['day' => $weekDay, 'hour' => $hour])
            ->get()
            ->reject(function ($duty) use ($currentDate, $mappedUsers) {
                // 1. Sprawdź czy wzorzec wypada w TYM konkretnym tygodniu
                if (! $duty->isDutyInWeek($currentDate)) {
                    return true; // odrzuć
                }

                // 2. Sprawdź zawieszenie (używamy copy(), aby nie zmieniać currentDate)
                $user = $mappedUsers->get($duty->user_id);
                if (! $user || $user->isSuspended($currentDate->copy())) {
                    return true; // odrzuć
                }

                return false; // zostaw w kolekcji
            })
            ->map(function ($duty) {
                return [
                    'user_id'   => $duty->user_id,
                    'duty_type' => $duty->duty_type,
                ];
            });
    }

    // public function isDutyInWeek(Carbon $startDate): bool {
    //     // Get the start and target week numbers
    //     $startWeek = $startDate->copy()->startOfWeek()->weekOfYear;
    //     $targetDate = $startDate->addDays(Helper::dayNumber($this->day));
    //     $targetWeek = $targetDate->copy()->startOfWeek()->weekOfYear;

    //     // Calculate the difference in weeks
    //     $weeksDifference = $targetWeek - $startWeek;

    //     // Check if the duty repeats in this week
    //     return $weeksDifference >= 0 && $weeksDifference % $this->repeat_interval === 0;
    // }

    public function isDutyInWeek(Carbon $startDate): bool
    {
        // 1. Sprowadzamy obie daty do początku ich tygodni, aby porównywać "pełne bloki" siedmiodniowe
        $patternStart = Carbon::parse($this->start_date)->startOfWeek();
        $currentWeek  = $startDate->copy()->startOfWeek();

        // 2. Jeśli sprawdzany tydzień jest wcześniejszy niż data rozpoczęcia wzorca, dyżur nie istnieje
        if ($currentWeek->lt($patternStart)) {
            return false;
        }

        // 3. Liczymy różnicę w tygodniach
        $weeksDifference = $patternStart->diffInWeeks($currentWeek);

        // 4. Sprawdzamy czy różnica jest podzielna przez interwał (np. co 2 tygodnie)
        return $weeksDifference % $this->repeat_interval === 0;
    }
}
