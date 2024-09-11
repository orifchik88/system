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
        Schema::create('monitoring_objects', function (Blueprint $table) {
            $table->id();
            $table->integer('monitoring_object_id')->nullable();
            $table->integer('project_type_id')->nullable();
            $table->text('name')->nullable();
            $table->string('gnk_id')->nullable();
            $table->string('end_term_work_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_objects');
    }
};
