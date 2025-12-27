<?php

use App\Services\Helper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'admin_duty_patterns';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable();
            $table->integer('hour');
            $table->string('day', 12);
            $table->timestamps();
        });

        $inserts = [];
        foreach(Helper::WEEK_DAYS as $day){
            foreach(Helper::DAY_HOURS as $hour){
                $inserts[] = ['hour' => $hour, 'day' => $day];
            }
        }

        DB::table(self::TABLE)->insert($inserts);
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};
