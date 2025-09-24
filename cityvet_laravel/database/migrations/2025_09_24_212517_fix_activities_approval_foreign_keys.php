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
        Schema::table('activities', function (Blueprint $table) {
            // Drop the existing foreign keys
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            
            // Add foreign keys pointing to admins table instead
            $table->foreign('approved_by')->references('id')->on('admins');
            $table->foreign('rejected_by')->references('id')->on('admins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop the admin foreign keys
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            
            // Restore the original user foreign keys
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('rejected_by')->references('id')->on('users');
        });
    }
};
