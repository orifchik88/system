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
        Schema::create('regulation_violation_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->nullable()->constrained();
            $table->foreignId('violation_id')->nullable()->constrained();
            $table->foreignId('block_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulation_violation_blocks');
    }
};
