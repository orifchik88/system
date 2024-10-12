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
        Schema::create('author_regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->foreignId('block_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('author_id')->nullable()->index();
            $table->integer('author_role_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->integer('role_id')->nullable()->index();
            $table->integer('bases_id')->nullable()->index();
            $table->integer('work_type_id')->nullable()->index();
            $table->jsonb('author_images')->nullable();
            $table->jsonb('images')->nullable();
            $table->text('author_comment')->nullable();
            $table->text('comment')->nullable();
            $table->date('deadline')->nullable();
            $table->integer('checklist_answer_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_regulations');
    }
};
