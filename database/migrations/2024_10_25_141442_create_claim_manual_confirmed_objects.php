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
        Schema::create('claim_manual_confirmed_objects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('object_id');
            $table->bigInteger('user_id');
            $table->string('comment');
            $table->string('file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_manual_confirmed_objects');
    }
};
