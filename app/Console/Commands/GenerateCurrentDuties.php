<?php
namespace App\Console\Commands;

use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCurrentDuties extends Command
{
    protected $signature   = 'app:generate-current-duties {--advance=} {--no_weeks=}';
    protected $description = 'Generate duties for the next 4 weeks';
    const ADVANCE          = 4;
    const NO_WEEKS         = 1;

    public function handle()
    {
        $startDate = Carbon::now()
            ->startOfWeek(Carbon::SUNDAY)
            ->addWeeks(intval($this->option('advance') ?? self::ADVANCE));

        $noOfWeeks = intval($this->option('no_weeks') ?? self::ADVANCE);

        $dateInserted = $startDate->copy();

        for ($week = 1; $week < $noOfWeeks; $week++) {
            foreach (Helper::WEEK_DAYS as $weekDay) {
                foreach (Helper::DAY_HOURS as $hour) {
                    $currentDuty       = new CurrentDuty();
                    $currentDuty->hour = $hour;
                    $currentDuty->date = $dateInserted;
                    $currentDuty->save();

                    if ($users = DutyPattern::getUsersForTimeFrame($startDate, $weekDay, $hour)->toArray()) {
                        $currentDuty->users()->attach($users);
                    }
                }
                $dateInserted->addDays(1);
            }
        }

        $this->info('Duties generated successfully!');
    }
}
