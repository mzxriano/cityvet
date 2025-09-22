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
        Schema::table('communities', function (Blueprint $table) {
            $table->boolean('reported')->default(false);
        });

        Schema::table('community_comments', function (Blueprint $table) {
            $table->boolean('reported')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('reported');
        });

        Schema::table('community_comments', function (Blueprint $table) {
            $table->dropColumn('reported');
        });
    }
};
