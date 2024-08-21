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
        Schema::create('monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_id')->constrained('articles');
            $table->integer('number')->nullable();
            $table->foreignId('regulation_type_id')->constrained();
//            $table->text('comment')->nullable()->after('created_by');
            $table->integer('created_by')->index();
            $table->string('dalolatnoma_pdf')->nullable();
            $table->string('code')->nullable();
            $table->string('dalolatnoma_number')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitorings');
    }
};
