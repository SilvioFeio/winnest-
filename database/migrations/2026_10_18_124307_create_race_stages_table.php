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
        Schema::create('race_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->enum('stage_type', ['training', 'hotspot', 'final']);
            $table->string('name');
            $table->unsignedInteger('participants');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_stages');
    }
};
