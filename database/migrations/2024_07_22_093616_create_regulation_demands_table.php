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
        Schema::create('regulation_demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('act_status_id')->constrained();
            $table->foreignId('act_violation_type_id')->constrained();
            $table->foreignId('act_violation_id')->nullable()->constrained();
            $table->string('deadline')->nullable();
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulation_demands');
    }
};
