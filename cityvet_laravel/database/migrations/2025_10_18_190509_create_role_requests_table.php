<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_role_id')->constrained('roles')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('set null');
            
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'requested_role_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_requests');
    }
};