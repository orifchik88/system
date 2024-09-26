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
            $table->dropColumn(['inspector_answered', 'technic_answered', 'author_answered']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_list_answers', function (Blueprint $table) {
        });
    }
};
