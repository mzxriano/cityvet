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
        Schema::create('animal_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'dog', 'cat', 'cattle'
            $table->string('display_name'); // e.g., 'Dog', 'Cat', 'Cattle'
            $table->string('category'); // 'pet', 'livestock', 'poultry'
            $table->string('icon')->nullable(); // Icon name or path
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_types');
    }
};
