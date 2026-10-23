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
        Schema::create('pigeons', function (Blueprint $table) {
            $table->id();
            $table->string('ring_number')->unique();
            $table->enum('sex' ,['cock', 'hen', 'unknown']);
            $table->string('colour')->nullable();
            $table->foreignId('parent_pair_id')->nullable()->constrained('pairs')->nullOnDelete();
            $table->enum('status',['alive','lost'])->default('alive');
            $table->date('status_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pigeons');
    }
};
