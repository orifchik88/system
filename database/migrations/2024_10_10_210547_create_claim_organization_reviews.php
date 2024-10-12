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
        Schema::create('claim_organization_reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('monitoring_id');
            $table->integer('organization_id');
            $table->timestamp('expiry_date');
            $table->text('answer')->nullable();
            $table->integer('expired')->default(0);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_organization_reviews');
    }
};
