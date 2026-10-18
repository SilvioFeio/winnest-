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
        Schema::create('pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cock_id');
            $table->foreignId('hen_id');
            $table->year('season');
            $table->timestamps();

            $table->unique(['cock_id', 'hen_id', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pairs');
    }
};
