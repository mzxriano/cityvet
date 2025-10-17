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
        // Add foreign keys to animals table
        Schema::table('animals', function (Blueprint $table) {
            $table->foreignId('animal_type_id')->nullable()->after('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('animal_breed_id')->nullable()->after('animal_type_id')->constrained()->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign(['animal_type_id']);
            $table->dropForeign(['animal_breed_id']);
            $table->dropColumn(['animal_type_id', 'animal_breed_id']);
        });
    }
};
