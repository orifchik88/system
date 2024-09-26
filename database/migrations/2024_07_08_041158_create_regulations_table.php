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
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('articles');
            $table->string('regulation_number');
            $table->string('deadline')->nullable();
            $table->integer('inspector_id')->nullable()->index();
            $table->foreignId('regulation_status_id')->nullable()->constrained();
            $table->foreignId('regulation_type_id')->nullable()->constrained();
            $table->foreignId('monitoring_id')->nullable()->constrained();
            $table->foreignId('act_status_id')->nullable()->constrained();
            $table->integer('user_id')->nullable()->index();
            $table->integer('role_id')->nullable()->index();
            $table->integer('created_by_user_id')->nullable()->index();
            $table->integer('created_by_role_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
