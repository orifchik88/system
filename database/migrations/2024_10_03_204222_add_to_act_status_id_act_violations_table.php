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
        Schema::table('act_violations', function (Blueprint $table) {
            $table->foreignId('act_status_id')->after('id')->nullable()->constrained('act_statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('act_status_id_act_violations', function (Blueprint $table) {
            //
        });
    }
};
