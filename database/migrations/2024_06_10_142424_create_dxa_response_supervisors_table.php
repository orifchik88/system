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
        Schema::create('dxa_response_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dxa_response_id')->constrained();
            $table->string('type')->nullable();
            $table->integer('role_id')->nullable()->index();
            $table->string('role')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('stir_or_pinfl')->nullable();
            $table->string('fish')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('name_graduate_study')->nullable();
            $table->string('specialization')->nullable();
            $table->string('diplom_number')->nullable();
            $table->string('diplom_date')->nullable();
            $table->string('sertificate_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dxa_response_supervisors');
    }
};
