<?php

use App\Models\WaysOfContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('password');
            $table->foreignId('ways_of_contacts_id');
            $table->date('suspend_from')->nullable();
            $table->date('suspend_to')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->rememberToken()->after('password')->nullable();
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
