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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_type_id')->nullable()->constrained()->nullOnDelete();
            $table->text('name')->nullable();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('type')->nullable()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
