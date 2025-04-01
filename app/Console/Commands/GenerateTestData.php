<?php

namespace App\Console\Commands;

use App\Enums\DutyType;
use App\Models\AdminDutyPattern;
use App\Models\DutyPattern;
use App\Models\Intention;
use App\Models\IntentionUser;
use App\Models\User;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class GenerateTestData extends Command
{
    protected $signature = 'app:td';
    protected $description = 'Generate test users and admins';

    public function handle()
    {
        $faker = fake('pl_PL');
        // Generate regular users
        for ($i = 1; $i <= 80; $i++) {
            if($i ==1 ){
                $user = User::create([
                    'first_name' => 'Marek',
                    'last_name' => $faker->lastName,
                    'email' => 'mmikusek@o2.pl',
                    'password' => Hash::make('test'),
                    'notification_preference' => rand(0, 1) ? 'email' : 'sms',
                    'phone_number' => '600075041',
                ]);
            } else {
                $user = User::create([
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'email' => $faker->email,
                    'password' => Hash::make('test'),
                    'notification_preference' => rand(0, 1) ? 'email' : 'sms',
                    'phone_number' => null,
                ]);
            }

            DutyPattern::create([
                'user_id' => $user->id,
                'hour' => array_rand(Helper::DAY_HOURS),
                'day' => Helper::WEEK_DAYS[random_int(0, 6)],
                'duty_type' => DutyType::DUTY->value,
                'start_date' => Carbon::now(),
                'repeat_interval' => Arr::random([1, 1, 1, 1, 1, 2, 3, 1 ,1]),
            ]);

            DutyPattern::create([
                'user_id' => $user->id,
                'hour' => array_rand(Helper::DAY_HOURS),
                'day' => Helper::WEEK_DAYS[random_int(0, 6)],
                'duty_type' => DutyType::READY->value,
                'start_date' => Carbon::now(),
                'repeat_interval' => Arr::random([1, 1, 1, 1, 1, 2, 3, 1, 1]),
            ]);

            DutyPattern::create([
                'user_id' => $user->id,
                'hour' => array_rand(Helper::DAY_HOURS),
                'day' => Helper::WEEK_DAYS[random_int(0, 6)],
                'duty_type' => DutyType::READY->value,
                'start_date' => Carbon::now(),
                'repeat_interval' => Arr::random([1, 1, 1, 1, 1, 2, 3, 1, 1]),
            ]);
        }

        $admindIds = [];
        $marek = User::create([
            'first_name' => 'Marek',
            'last_name' => 'Mikusek',
            'email' => 'mmikusek2211@gmail.com',
            'password' => Hash::make('test'),
            'notification_preference' => rand(0, 1) ? 'email' : 'sms',
            'is_admin' => true,
            'phone_number' => null,
            'color' => sprintf("#%06X", mt_rand(0, 0xFFFFFF))
        ]);
        // Generate admin users
        for ($i = 1; $i <= 4; $i++) {
            $adminId = User::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
                'password' => Hash::make('test'),
                'notification_preference' => rand(0, 1) ? 'email' : 'sms',
                'is_admin' => true,
                'phone_number' => null,
                'color' => sprintf("#%06X", mt_rand(0, 0xFFFFFF))
            ]);

            $admindIds[] = $adminId->id;
        }

        foreach(AdminDutyPattern::all() as $adminDutyPattern){
            $adminDutyPattern->admin_id = $admindIds[random_int(0, 3)];
            $adminDutyPattern->save();
        }

        for($i = 0; $i<= 20; $i++) {
            $intention = Intention::create([
                'intention' => $faker->sentence(8, true)
            ]);

            $randomNumberOfUsers = Arr::random([1, 1, 1, 0, 0, 2, 3, 4, 1]);

            for($j = 0; $j<= $randomNumberOfUsers; $j++){
                IntentionUser::create(['intention_id' => $intention->id, 'user_id' => random_int(1, 80)]);
            }

        }

        $this->info('Test users and admins generated successfully!');
    }
}
