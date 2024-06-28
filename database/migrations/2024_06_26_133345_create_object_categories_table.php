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
        Schema::create('object_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz')->nullable();
            $table->string('name_ru')->nullable();
            $table->string('description')->nullable();
            $table->integer('shq_id')->nullable();
            $table->foreignId('funding_source_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_categories');
    }
};
