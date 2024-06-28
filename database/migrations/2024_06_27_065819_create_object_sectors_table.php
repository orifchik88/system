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
        Schema::create('object_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz')->nullable();
            $table->string('name_ru')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('object_type_id')->nullable()->constrained();
            $table->integer('shq_id')->nullable();
            $table->integer('min_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_sectors');
    }
};
