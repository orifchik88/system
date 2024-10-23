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
        Schema::create('regulation_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('from_user_id')->nullable()->index();
            $table->integer('from_role_id')->nullable()->index();
            $table->integer('to_user_id')->nullable()->index();
            $table->integer('to_role_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulation_users');
    }
};
