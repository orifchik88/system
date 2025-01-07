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
        Schema::create('regulation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->onDelete('cascade');
            $table->integer('status')->nullable();
            $table->text('comment')->nullable();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->bigInteger('role_id')->unsigned()->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulation_events');
    }
};
