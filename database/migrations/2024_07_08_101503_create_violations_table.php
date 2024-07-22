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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained();
            $table->text('title');
            $table->text('description');
            $table->string('doc')->nullable();
            $table->integer('status_id')->nullable()->comment('Bu status id ishlayilmaydi shekilli yana bir tekwirib korish kerak');
            $table->foreignId('question_id')->nullable()->constrained();
            $table->boolean('check_list_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
