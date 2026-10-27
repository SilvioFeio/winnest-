<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Databases created before 'unknown' was added to the pigeons migration still only allow cock/hen
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pigeons', function (Blueprint $table) {
            $table->enum('sex', ['cock', 'hen', 'unknown'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Leaves 'unknown' in place: removing it would fail as soon as a pigeon has that value
    }
};
