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
            $table->integer('role_id')->index()->nullable();
            $table->integer('regulation_violation_id')->index()->nullable();
            $table->foreignId('violation_id')->nullable()->change();
        });
        Schema::table('regulation_demands', function (Blueprint $table) {
            $table->integer('role_id')->index()->nullable();
            $table->integer('regulation_violation_id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
