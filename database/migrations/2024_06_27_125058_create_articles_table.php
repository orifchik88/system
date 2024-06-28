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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('region_id')->constrained();
            $table->foreignId('district_id')->constrained();
            $table->foreignId('object_status_id')->constrained();
            $table->string('address')->nullable();
            $table->string('cadastral_number')->nullable();
            $table->string('location_building')->nullable();
            $table->string('name_expertise')->nullable();
            $table->foreignId('difficulty_category_id')->nullable()->constrained();
            $table->string('construction_cost')->nullable();
            $table->string('object_images')->nullable()->comment('relation table ochib ketadi');
            $table->string('blocks')->nullable()->comment('relation table ochib ketadi');
            $table->string('additional_categories')->nullable()->comment('Bilmadm keyin korib chiqish kerak');
            $table->foreignId('object_type_id')->nullable()->constrained();
            $table->integer('property_type')->nullable()->comment('manimcha kerak emas');
            $table->string('architectural_number_date_protocol')->nullable()->comment('bilmiman bosh turibdi');
            $table->string('parallel_designobjc')->nullable()->comment('kopchiligida  bosh turibdi');
            $table->string('objects_stateprog')->nullable()->comment('kopchiligida  bosh turibdi');
            $table->string('name_date_posopin')->nullable()->comment('bilmiman bosh turibdi');
            $table->string('name_date_licontr')->nullable()->comment('kopchiligida  bosh turibdi');
            $table->boolean('is_accepted')->default(false);
            $table->string('organization_projects')->nullable()->comment('file');
            $table->string('specialists_certificates')->nullable()->comment('file');
            $table->string('contract_file')->nullable()->comment('file');
            $table->string('confirming_laboratory')->nullable()->comment('file');
            $table->string('file_energy_efficiency')->nullable()->comment('file');
            $table->string('legal_opf')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('authority_id')->nullable();
            $table->boolean('lat_long_status')->default(false);
            $table->integer('dxa_response_id')->index();
            $table->integer('company_id')->nullable()->comment('bosh');
            $table->integer('applicant_id')->nullable()->comment('bosh');
            $table->float('price_supervision_service')->nullable()->comment('0.2 foiz');
            $table->integer('task_id')->nullable()->index();
            $table->foreignId('costumer_id')->nullable()->constrained();
            $table->integer('number_protocol')->nullable();
            $table->string('positive_opinion_number')->nullable();
            $table->string('positive_opinion_date')->nullable();
            $table->string('date_protocol')->nullable();
            $table->foreignId('object_specific_id')->nullable()->comment('object types tabledagi idlar')->constrained();
            $table->foreignId('funding_source_id')->nullable()->constrained();
            $table->boolean('re_formalized_object')->default(false);
            $table->float('paid')->default(0);
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('object_sector_id')->nullable()->constrained();
            $table->foreignId('object_category_id')->nullable()->constrained();
            $table->string('deadline')->nullable();
            $table->string('update_by')->nullable();
            $table->integer('block_status_counter')->nullable();
            $table->integer('costumer_cer_num')->nullable();
            $table->integer('planned_object_id')->nullable();
            $table->integer('min_ekonom_id')->nullable();
            $table->integer('gnk_id')->nullable();
            $table->boolean('t_is_changed')->default(false);
            $table->string('reestr_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
