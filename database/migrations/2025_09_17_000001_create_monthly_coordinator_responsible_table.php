<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'monthly_coordinators_patterns';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->foreignId('coordinator_responsible')
                ->nullable()
                ->constrained('users');
            $table->tinyInteger('day')->nullable();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users');
            $table->timestamps();
        });

        $inserts = [];
        foreach (range(1, 31) as $day) {
            $inserts[] = ['day' => $day];
        }

        DB::table(self::TABLE)->insert($inserts);
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};
