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
        Schema::create('claim_monitoring', function (Blueprint $table) {
            $table->id();
            $table->json('blocks');
            $table->json('organizations');
            $table->text('operator_answer')->nullable();
            $table->bigInteger('claim_id');
            $table->smallInteger('inspector_answer')->default(0);
            $table->smallInteger('director_answer')->default(0);
            $table->bigInteger('object_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_monitoring');
    }
};
