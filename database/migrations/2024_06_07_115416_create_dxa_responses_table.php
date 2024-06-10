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
        Schema::create('dxa_responses', function (Blueprint $table) {
            $table->id();
            $table->json('task')->nullable();
            $table->text('inspector_commit')->nullable();
            $table->jsonb('inspector_images')->nullable();
            $table->boolean('is_accepted')->default(false);
            $table->foreignId('dxa_response_statuses_id')->nullable()->constrained();
            $table->integer('user_id')->nullable();
            $table->text('rejection_comment')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->json('administrative_files')->nullable();
            $table->foreignId('object_statuses_id')->nullable()->constrained();
            $table->integer('task_id')->nullable();
            $table->integer('old_task_id')->nullable();
            $table->text('object_name')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('notification_type')->nullable();
            $table->string('cadastral_number')->nullable();
            $table->integer('application_stir_pinfl')->nullable();
            $table->string('application_name')->nullable();
            $table->string('current_note')->nullable();
            $table->string('dxa_status')->nullable();
            $table->string('number_protocol')->nullable();
            $table->string('technic_org_name')->nullable();
            $table->dateTime('inspector_sent_at')->nullable();
            $table->dateTime('inspector_answered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dxa_responses');
    }
};
