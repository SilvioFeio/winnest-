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
        Schema::create('fertility_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pair_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('eggs_laid')->default(0);
            $table->unsignedInteger('fertile_eggs')->default(0);
            $table->unsignedInteger('hatched')->default(0);
            $table->unsignedInteger('weaned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fertility_records');
    }
};
