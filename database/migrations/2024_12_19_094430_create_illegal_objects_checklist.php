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
        Schema::create('illegal_objects_checklist', function (Blueprint $table) {
            $table->id();
            $table->integer('question_id');
            $table->integer('object_id');
            $table->boolean('answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('illegal_objects_checklist');
    }
};
