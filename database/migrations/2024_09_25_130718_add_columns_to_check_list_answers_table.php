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
        Schema::table('check_list_answers', function (Blueprint $table) {
            $table->boolean('inspector_answered')->default(false);
            $table->boolean('technic_answered')->default(false);
            $table->boolean('author_answered')->default(false);
            $table->integer('check_list_status_id')->nullable()->index();
            $table->date('inspector_answered_at')->nullable();
            $table->date('technic_answered_at')->nullable();
            $table->date('author_answered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_list_answers', function (Blueprint $table){
            //
        });
    }
};
