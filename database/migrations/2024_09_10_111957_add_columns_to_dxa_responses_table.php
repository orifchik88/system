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
        Schema::table('dxa_responses', function (Blueprint $table) {
            $table->bigInteger('gnk_id')->nullable()->comment('Monitoringdan kelgan object');
            $table->foreignId('funding_source_id')->nullable()->comment('Moliyalashtirish manbai')->constrained()->onDelete('set null');
            $table->string('price_supervision_service')->nullable()->comment('Tolanadigan pul miqdori');
            $table->string('end_term_work')->nullable()->comment('Obyekt tugallanishi kerak bolagan sana');
            $table->integer('sphere_id')->nullable()->comment('Soha idsi');
            $table->integer('program_id')->nullable()->comment('Dastur idsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dxa_responses', function (Blueprint $table) {
            //
        });
    }
};
