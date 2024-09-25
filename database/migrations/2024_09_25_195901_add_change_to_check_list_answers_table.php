<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('check_list_answers', function (Blueprint $table) {
            $table->integer('inspector_answered')->nullable()->change();
            $table->integer('technic_answered')->nullable()->change();
            $table->integer('author_answered')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_list_answers', function (Blueprint $table) {
            //
        });
    }
};
