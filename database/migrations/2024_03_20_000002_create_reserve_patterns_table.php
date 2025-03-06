<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserve_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('hour');
            $table->string('day', 12);
            $table->tinyInteger('repeat_interval')->default(1); // weekly, biweekly, triweekly
            $table->date('start_date')->nullable();
            $table->date('suspension_begin')->nullable();
            $table->date('suspension_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserve_patterns');
    }
};
