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
            $table->text('organization_name')->nullable();
            $table->text('location_building')->nullable();
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
