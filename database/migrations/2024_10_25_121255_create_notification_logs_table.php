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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->nullable();
            $table->integer('user_id')->nullable()->index();
            $table->text('title')->nullable();
            $table->text('message')->nullable();
            $table->text('image_url')->nullable();
            $table->jsonb('additional_data')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('send_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
