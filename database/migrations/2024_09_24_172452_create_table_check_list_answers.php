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
        Schema::create('check_list_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('block_id');
            $table->integer('work_type_id');
            $table->integer('question_id');
            $table->integer('object_id');
            $table->integer('object_type_id');
            $table->integer('floor')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_check_list_answers');
    }
};
