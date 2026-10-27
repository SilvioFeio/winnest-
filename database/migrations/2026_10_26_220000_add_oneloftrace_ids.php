<?php

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
        Schema::table('races', function (Blueprint $table) {
            $table->unsignedBigInteger('olr_season_id')->nullable()->unique();
        });

        Schema::table('race_stages', function (Blueprint $table) {
            $table->unsignedBigInteger('olr_flight_id')->nullable()->unique();
            $table->timestamp('olr_synced_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_stages', function (Blueprint $table) {
            $table->dropUnique(['olr_flight_id']);
            $table->dropColumn(['olr_flight_id', 'olr_synced_at']);
        });

        Schema::table('races', function (Blueprint $table) {
            $table->dropUnique(['olr_season_id']);
            $table->dropColumn('olr_season_id');
        });
    }
};
