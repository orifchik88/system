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
//            $table->foreignId('regulation_id')->nullable()->constrained('regulations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('act_violations', function (Blueprint $table) {
            //
        });
    }
};
