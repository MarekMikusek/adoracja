<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('current_duties_users', function (Blueprint $table) {
            $table->tinyInteger('from_pattern')->default(0);
            $table->tinyInteger('changed_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('current_duties_users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('from_pattern');
            $table->dropColumn('changed_by');
        });
    }
};
