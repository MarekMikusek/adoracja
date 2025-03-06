<?php

namespace App\Console\Commands;

use App\Models\AdminDutyPattern;
use App\Models\DutyPattern;
use App\Models\ReservePattern;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class GenerateTestUsers extends Command
{
    protected $signature = 'app:generate-test-users';
    protected $description = 'Generate test users and admins';

    public function handle()
    {
        // Generate regular users
        for ($i = 1; $i <= 80; $i++) {
            $user = User::create([
                'first_name' => fake()->firstName,
                'last_name' => fake()->lastName,
                'email' => fake()->email,
                'password' => Hash::make('test'),
                'notification_preference' => rand(0, 1) ? 'email' : 'sms',
                'is_confirmed' => true,
                'phone_number' => null,
            ]);

            DutyPattern::create([
                'user_id' => $user->id,
                'hour' => array_rand(Helper::DAY_HOURS),
                'day' => Helper::WEEK_DAYS[random_int(0, 6)],
                'start_date' => Carbon::now(),
                'repeat_interval' => Arr::random([1, 1, 1, 1, 1, 2, 3, 1 ,1]),
            ]);

            ReservePattern::create([
                'user_id' => $user->id,
                'hour' => array_rand(Helper::DAY_HOURS),
                'day' => Helper::WEEK_DAYS[random_int(0, 6)],
                'start_date' => Carbon::now(),
                'repeat_interval' => Arr::random([1, 1, 1, 1, 1, 2, 3, 1, 1]),
            ]);

            ReservePattern::create([
                'user_id' => $user->id,
                'hour' => array_rand(Helper::DAY_HOURS),
                'day' => Helper::WEEK_DAYS[random_int(0, 6)],
                'start_date' => Carbon::now(),
                'repeat_interval' => Arr::random([1, 1, 1, 1, 1, 2, 3, 1, 1]),
            ]);
        }

        // Generate admin users
        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'first_name' => 'Admin',
                'last_name' => $i,
                'email' => fake()->email,
                'password' => Hash::make('test'),
                'notification_preference' => rand(0, 1) ? 'email' : 'sms',
                'is_admin' => true,
                'is_confirmed' => true,
                'phone_number' => null,
            ]);
        }

        foreach(AdminDutyPattern::all() as $adminDutyPattern){
            $adminDutyPattern->admin_id = random_int(1, 4);
            $adminDutyPattern->save();
        }

        $this->info('Test users and admins generated successfully!');
    }
}
