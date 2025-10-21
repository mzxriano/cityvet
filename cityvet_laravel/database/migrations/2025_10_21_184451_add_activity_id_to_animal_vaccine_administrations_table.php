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
        Schema::table('animal_vaccine_administrations', function (Blueprint $table) {
            // Adds the activity_id column
            $table->foreignId('activity_id')
                  ->nullable() 
                  ->constrained('activities')
                  ->onDelete('set null') 
                  ->after('vaccine_lot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animal_vaccine_administrations', function (Blueprint $table) {

            $table->dropConstrainedForeignId('activity_id');

        });
    }
};