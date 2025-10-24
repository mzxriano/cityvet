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
        Schema::create('activity_barangay', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            
            $table->foreignId('barangay_id')->constrained()->onDelete('cascade');
            
            $table->primary(['activity_id', 'barangay_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_barangay');
    }
};
