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
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Add foreign key constraints
            $table->foreign('approved_by')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            
            // Then drop columns
            $table->dropColumn([
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'rejection_reason'
            ]);
        });
    }
};
