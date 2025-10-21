<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccine_lots', function (Blueprint $table) {
            $table->id();

            // Link to the Parent Product
            $table->foreignId('vaccine_product_id')->constrained('vaccine_products')->cascadeOnDelete();

            // Lot-Specific Critical Data (CRITICAL for traceability and FEFO)
            $table->string('lot_number')->comment('Manufacturer assigned batch number');
            $table->date('expiration_date');
            
            // Stock and Receipt Data
            $table->integer('initial_stock')->default(0)->comment('Total doses received in this lot');
            $table->integer('current_stock')->default(0)->comment('Remaining doses in this lot');
            $table->date('received_date');
            
            // Inventory Management Details
            $table->string('storage_location')->nullable()->comment('Specific location, e.g., "Fridge A Shelf 2"');
            
            // Ensures a Lot Number is unique for a given Product (optional, but good practice)
            $table->unique(['vaccine_product_id', 'lot_number']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_lots');
    }
};