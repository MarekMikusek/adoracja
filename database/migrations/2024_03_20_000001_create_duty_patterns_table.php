<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('day', 12); // niedziela-sobota
            $table->tinyInteger('hour'); //0-23
            $table->tinyInteger('repeat_interval')->default(1);
            $table->date('start_date')->nullable();
            $table->date('suspend_from')->nullable();
            $table->date('suspend_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_patterns');
    }
};
