<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccine_stock_adjustments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('vaccine_lot_id')->constrained('vaccine_lots')->onDelete('cascade'); 
            
            $table->string('adjustment_type')->comment('e.g., Wastage, Spoilage, Inventory Error');
            $table->integer('quantity');
            $table->text('reason');
            $table->string('administrator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_stock_adjustments');
    }
};
