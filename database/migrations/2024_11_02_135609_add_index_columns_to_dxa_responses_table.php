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
            $table->string('user_type')->nullable()->index()->change();
            $table->integer('region_id')->nullable()->index()->change();
            $table->integer('district_id')->nullable()->index()->change();
            $table->integer('inspector_id')->nullable()->index()->change();
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
