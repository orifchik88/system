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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('phone')->nullable();
            $table->string('login')->nullable();
            $table->string('middle_name')->nullable();
            $table->text('organization_name')->nullable();
            $table->string('password');
            $table->tinyInteger('active')->default(0);
            $table->integer('created_by')->index()->nullable();
            $table->string('image')->nullable();
            $table->string('pinfl')->index()->nullable();
            $table->foreignId('user_status_id')->constrained();
            $table->text('address')->nullable();
            $table->string('identification_number')->nullable();
            $table->tinyInteger('type')->nullable()->comment('1-Jismoniy shaxs, 2- yuridik shaxs');
            $table->integer('parent_id')->nullable()->comment('yuridik shaxs id raqami');
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('district_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
