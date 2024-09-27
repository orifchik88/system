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
        Schema::table('regulations', function (Blueprint $table) {
            $table->integer('question_id')->index()->nullable();
            $table->integer('checklist_id')->index()->comment('Bu check_list_answer idsi')->nullable();
        });
        Schema::table('violations', function (Blueprint $table) {
            $table->integer('checklist_id')->index()->comment('Bu check_list_answer idsi')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            //
        });
    }
};
