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
        Schema::table('activities', function (Blueprint $table) {
            // Add created_by column
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Update the status enum to include 'pending'
        DB::statement("ALTER TABLE activities MODIFY status ENUM('pending', 'up_coming', 'on_going', 'completed', 'failed') DEFAULT 'up_coming'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop the foreign key and column
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        // Revert the status enum to original values
        DB::statement("ALTER TABLE activities MODIFY status ENUM('up_coming', 'on_going', 'completed', 'failed') DEFAULT 'up_coming'");
    }
};
