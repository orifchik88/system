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
        Schema::create('act_violation_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('act_violation_id')->constrained()->onDelete('cascade');
            $table->foreignId('block_id')->constrained();
            $table->text('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('act_violation_blocks');
    }
};