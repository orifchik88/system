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
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('block_mode_id')->constrained();
            $table->foreignId('block_type_id')->constrained();
            $table->foreignId('article_id')->nullable()->constrained();
            $table->foreignId('dxa_response_id')->nullable()->constrained();
            $table->string('floor')->nullable()->comment('Qavat');
            $table->string('construction_area')->nullable()->comment('Qurilish maydoni');
            $table->string('count_apartments')->nullable()->comment('Xonadonlar soni');
            $table->string('height')->nullable()->comment('Inshoat balandligi');
            $table->string('length')->nullable()->comment('Tarmoq uzunligi');
            $table->integer('created_by');
            $table->boolean('status')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
