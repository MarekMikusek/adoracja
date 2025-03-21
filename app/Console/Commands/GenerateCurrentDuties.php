<?php
namespace App\Console\Commands;

use App\Models\CurrentDuty;
use App\Models\DutyPattern;
use App\Models\User;
use App\Services\Helper;
use App\Services\DutiesService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCurrentDuties extends Command
{
    protected $signature   = 'app:generate-current-duties {--no_weeks=}';
    protected $description = 'Generate duties for no_weeks weeks';
    const NO_WEEKS = 1;

    public function handle()
    {
        $users = User::all();

        $startDate = (new Carbon(CurrentDuty::max('date')))->addDays(1);

        $noOfWeeks = intval($this->option('no_weeks') ?? self::NO_WEEKS);

        DutiesService::generateCurrentDuties($users, $startDate, $noOfWeeks);

        $this->info('Duties generated successfully!');
    }
}
