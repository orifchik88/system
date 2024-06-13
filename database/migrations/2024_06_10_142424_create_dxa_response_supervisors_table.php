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
            $table->string('type');
            $table->string('role');
            $table->string('organization_name');
            $table->string('identification_number');
            $table->string('pinfl');
            $table->string('fish');
            $table->string('passport_number');
            $table->string('name_graduate_study');
            $table->string('specialization');
            $table->string('diplom_number');
            $table->string('diplom_date');
            $table->string('sertificate_number');
            $table->string('phone_number');
            $table->text('comment');

            $table->timestamps();
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
