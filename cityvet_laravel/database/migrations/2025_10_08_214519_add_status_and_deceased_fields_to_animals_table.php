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
            $table->enum('status', ['alive', 'deceased', 'missing', 'transferred'])->default('alive')->after('image_public_id');
            $table->date('deceased_date')->nullable()->after('status');
            $table->string('deceased_cause')->nullable()->after('deceased_date');
            $table->text('deceased_notes')->nullable()->after('deceased_cause');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn(['status', 'deceased_date', 'deceased_cause', 'deceased_notes']);
        });
    }
};
