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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('guid')->unique();
            $table->bigInteger('object_id')->nullable();
            $table->string('created_date_mygov')->nullable();
            $table->string('updated_date_mygov')->nullable();
            $table->string('user_type')->nullable();
            $table->string('property_owner')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('legal_tin')->nullable();
            $table->string('legal_email')->nullable();
            $table->text('legal_address')->nullable();
            $table->string('legal_phone')->nullable();
            $table->string('building_name')->nullable();

            $table->bigInteger('region')->nullable();
            $table->bigInteger('district')->nullable();
            $table->string('building_cadastral')->nullable();
            $table->string('building_address')->nullable();
            $table->integer('building_type')->nullable();
            $table->string('ind_pinfl')->nullable();
            $table->string('ind_passport')->nullable();
            $table->string('ind_address')->nullable();
            $table->string('ind_name')->nullable();
            $table->string('tin_project_organization')->nullable();
            $table->string('document_registration_based')->nullable();
            $table->string('object_project_user')->nullable();
            $table->string('number_conclusion_project')->nullable();
            $table->integer('type_object_dic')->nullable();

            $table->string('cadastral_passport_object_file')->nullable();
            $table->string('ownership_document')->nullable();
            $table->string('act_acceptance_customer_file')->nullable();
            $table->string('declaration_conformity_file')->nullable();
            $table->string('conclusion_approved_planning_file')->nullable();

            $table->integer('status')->default(1)->nullable();
            $table->string('status_mygov')->nullable();
            $table->integer('expired')->default(0);
            $table->string('current_node')->nullable();
            $table->string('operator_org')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
