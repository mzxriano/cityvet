<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vaccine_products', function (Blueprint $table) {

            $table->dropUnique(['name']);

            $table->dropColumn('affected');

            $table->foreignId('affected_id')
                  ->nullable()
                  ->constrained('animal_types') 
                  ->after('protect_against') 
                  ->onDelete('set null') 
                  ->comment('Foreign key to animal_types, nullable for general products');

            $table->unique(['name', 'affected_id']);
        });
    }

    public function down(): void
    {
        // Revert changes in case of rollback
        Schema::table('vaccine_products', function (Blueprint $table) {
            
            $table->dropUnique(['name', 'affected_id']);
            
            $table->dropForeign(['affected_id']);
            $table->dropColumn('affected_id');
            
            $table->string('affected')->nullable()->after('protect_against');
            
            $table->unique('name');
        });
    }
};