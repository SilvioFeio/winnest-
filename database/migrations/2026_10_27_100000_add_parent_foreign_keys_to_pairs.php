<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// pairs is created before pigeons, so the cock/hen foreign keys can only be added afterwards
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pairs', function (Blueprint $table) {
            $table->foreign('cock_id')->references('id')->on('pigeons')->restrictOnDelete();
            $table->foreign('hen_id')->references('id')->on('pigeons')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pairs', function (Blueprint $table) {
            $table->dropForeign(['cock_id']);
            $table->dropForeign(['hen_id']);
        });
    }
};
