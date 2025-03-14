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
        Schema::create('object_user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('articles');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('role_id')->constrained('roles');
            $table->date('check_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_user_histories');
    }
};
