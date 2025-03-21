<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('current_duties', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('hour');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('current_duties');
    }
};
