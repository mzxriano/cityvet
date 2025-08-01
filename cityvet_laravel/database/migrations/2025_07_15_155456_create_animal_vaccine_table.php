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
        Schema::create('animal_vaccine', function (Blueprint $table) {
            $table->id();
            $table->integer('dose');
            $table->date('date_given')->nullable();
            $table->string('administrator')->nullable();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            $table->foreignId('vaccine_id')->constrained('vaccines')->onDelete('cascade');
            $table->timestamps();

           $table->unique(['dose', 'animal_id', 'vaccine_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_vaccine');
    }
};
