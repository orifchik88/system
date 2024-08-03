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
        Schema::create('act_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->nullable()->constrained('regulations');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('violation_id')->constrained();
            $table->text('comment')->nullable();
            $table->integer('question_id')->index()->nullable();
            $table->foreignId('act_violation_type_id')->constrained();
            $table->tinyInteger('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('act_violations');
    }
};
