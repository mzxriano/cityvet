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
        Schema::table('animals', function (Blueprint $table) {
            // Modify the status enum to include 'deleted'
            $table->enum('status', ['alive', 'deceased', 'missing', 'transferred', 'deleted'])
                  ->default('alive')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['alive', 'deceased', 'missing', 'transferred'])
                  ->default('alive')
                  ->change();
        });
    }
};
