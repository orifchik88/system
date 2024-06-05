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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('project_id')->nullable();
            $table->integer('client_platform_id')->nullable();
            $table->foreignId('client_type_id')->constrained();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('login')->unique();
            $table->string('password');
            $table->tinyInteger('active')->default(0);
            $table->string('image')->nullable();
            $table->string('pinfl');
            $table->foreignId('user_status_id')->constrained();
            $table->string('doc_number')->nullable();
            $table->string('diplom')->nullable();
            $table->string('objective')->nullable();
            $table->string('attestation')->nullable();
            $table->string('nps')->nullable();
            $table->string('surname')->nullable();
            $table->string('middle_name')->nullable();
            $table->text('address')->nullable();
            $table->string('passport_number')->nullable();
            $table->foreignId('region_id')->constrained();
            $table->foreignId('district_id')->constrained();
            $table->string('organization_name')->nullable();
            $table->integer('company_id')->nullable();
            $table->foreignId('user_type_id')->constrained();
            $table->boolean('is_activated')->default(false);
            $table->integer('shq_id')->nullable();
            $table->string('stir_org')->nullable();
            $table->string('datenm_contract')->nullable();
            $table->string('name_graduate_study')->nullable();
            $table->string('diplom_number')->nullable();
            $table->string('date_issue_diploma')->nullable();
            $table->string('certificate_courses')->nullable();
            $table->string('specialization')->nullable();
            $table->string('role_in_object')->nullable();
            $table->string('previous_pinfl')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
