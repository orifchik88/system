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
        Schema::create('dxa_responses', function (Blueprint $table) {
            $table->id();
            $table->json('task')->nullable();
            $table->text('inspector_commit')->nullable();
            $table->integer('inspector_id')->nullable();
            $table->jsonb('inspector_images')->nullable();
            $table->boolean('is_accepted')->default(false);
            $table->foreignId('dxa_response_statuses_id')->nullable()->constrained();
            $table->integer('user_id')->nullable();
            $table->string('user_type')->nullable();
            $table->string('email')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('rejection_comment')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('address')->nullable();
            $table->string('legal_opf')->nullable()->comment('Tashkiliy-huquqiy shakli');
            $table->dateTime('deadline')->nullable();
            $table->json('administrative_files')->nullable();
            $table->foreignId('administrative_statuses_id')->nullable()->constrained();
            $table->integer('task_id')->nullable();
            $table->integer('old_task_id')->nullable();
            $table->text('object_name')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->string('pinfl')->nullable();
            $table->string('full_name')->nullable();
            $table->string('passport')->nullable();
            $table->string('permit_address')->nullable()->comment('Jismoniy shaxs manzili');
            $table->integer('notification_type')->nullable();
            $table->string('cadastral_number')->nullable();
            $table->string('reestr_number')->nullable()->comment('reyestr raqami');
            $table->string('tip_object')->nullable()->comment('Bino turi');
            $table->string('vid_object')->nullable()->comment('Bino ko\'rinishi');
            $table->string('location_building')->nullable()->comment('Bino joylashuvi');
            $table->integer('application_stir_pinfl')->nullable();
            $table->string('application_name')->nullable();
            $table->string('current_note')->nullable();
            $table->string('dxa_status')->nullable();
            $table->string('cost')->nullable()->comment('Qurilish-montaj ishlarining qiymati:');
            $table->string('number_protocol')->nullable()->comment('Arxitektura-shaharsozlik kengashi bayonnomasining raqami');
            $table->string('date_protocol')->nullable()->comment('Arxitektura-shaharsozlik kengashi bayonnomasining sanasi');
            $table->string('technic_org_name')->nullable()->comment('Tashkilot nomi');
            $table->integer('category_object_dictionary')->nullable()->comment('Obyekt murakkabligi kategoriyasi');
            $table->string('construction_works')->nullable()->comment('Qurilish ishlari turi');
            $table->string('object_parallel_design_number')->nullable()->comment('Parallel loyihalash ob\'ektlari uchun raqami');
            $table->string('object_parallel_design_date')->nullable()->comment('Parallel loyihalash ob\'ektlari uchun sana');
            $table->string('object_state_program_number')->nullable()->comment('Davlat dasturi obʼyektlari boʻyicha raqami');
            $table->string('object_state_program_date')->nullable()->comment('Davlat dasturi obʼyektlari boʻyicha sana');
            $table->string('name_expertise')->nullable()->comment('Davlat ekspertiza organining nomi');
            $table->string('positive_opinion_number')->nullable()->comment('Davlat ekspertiza organining ijobiy xulosasi raqami');
            $table->string('contractor_license_number')->nullable()->comment('Qurilish-montaj ishlarining ayrim litsenziyalanadigan litsenziya raqami');
            $table->string('contractor_license_date')->nullable()->comment('Qurilish-montaj ishlarining ayrim litsenziyalanadigan litsenziya sanasi');
            $table->string('industrial_security_number')->nullable()->comment('Xavfli ishlab chiqarish ob\'ektlarining loyiha hujjatlarining sanoat xavfsizligi bo\'yicha ekspertiza organlarining ijobiy xulosasining raqami');
            $table->string('industrial_security_date')->nullable()->comment('Xavfli ishlab chiqarish ob\'ektlarining loyiha hujjatlarining sanoat xavfsizligi bo\'yicha ekspertiza organlarining ijobiy xulosasining sana');
            $table->string('confirming_laboratory')->nullable()->comment('Qurilish labaratoriyalar mavjudligi haqidagi hujjat');
            $table->string('specialists_certificates')->nullable()->comment('Buyurtmachining texnik nazorati sertifikatlari');
            $table->string('contract_file')->nullable()->comment('Buyurtmachining texnik nazorati shartnoma nusxalari');
            $table->string('organization_projects')->nullable()->comment('Qurilishni tashkil etish va ishlarni bajarishning tasdiqlangan loyihalari');
            $table->string('file_energy_efficiency')->nullable()->comment('"Energiya samaradorlik" hujjati');
            $table->dateTime('inspector_sent_at')->nullable();
            $table->dateTime('inspector_answered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dxa_responses');
    }
};
