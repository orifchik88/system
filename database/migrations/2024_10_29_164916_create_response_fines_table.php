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
        Schema::create('response_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dxa_response_id')->constrained()->nullOnDelete();
            $table->tinyInteger('user_type')->index();
            $table->text('organization_name')->nullable();
            $table->bigInteger('inn')->nullable();
            $table->string('full_name')->nullable();
            $table->bigInteger('pinfl')->nullable();
            $table->string('position')->nullable();
            $table->string('decision_series')->nullable();
            $table->bigInteger('decision_number')->nullable();
            $table->string('substance')->nullable()->comment('MJTK moddasi');
            $table->string('substance_item')->nullable()->comment('modda bandi');
            $table->string('amount')->nullable()->comment('jarima miqdori');
            $table->date('date')->nullable()->comment('qollanilgan sana');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_fines');
    }
};
