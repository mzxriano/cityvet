<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renamed from 'vaccines' to 'vaccine_products'
        Schema::create('vaccine_products', function (Blueprint $table) {
            $table->id();

            // Generic Product Information (Static)
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->enum('category', ['vaccine', 'deworming', 'vitamin']);
            $table->string('protect_against')->nullable();
            $table->string('affected')->nullable(); // Target species/group

            // Critical Veterinary/Inventory Metadata
            $table->enum('storage_temp', ['refrigerated', 'frozen', 'ambient'])
                  ->default('refrigerated')
                  ->comment('Required storage condition');
            $table->integer('withdrawal_days')->default(0)->comment('Days before animal product is safe for consumption (Livestock)');
            $table->string('unit_of_measure')->default('vial')->comment('Unit of stock (dose, vial, ml)');

            // Image/Media
            $table->string('image_url')->nullable();
            $table->string('image_public_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_products');
    }
};