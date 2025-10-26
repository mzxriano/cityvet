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
        Schema::create('animal_vaccine_administrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('animal_id')->constrained('animals')->cascadeOnDelete(); 

            $table->foreignId('vaccine_lot_id')->constrained('vaccine_lots')->cascadeOnDelete();

            // Administration Details
            $table->double('doses_given')->default(1);
            $table->date('date_given');
            $table->string('administrator')->nullable();
            $table->enum('route_of_admin', ['IM', 'SC', 'IV', 'Oral', 'Other'])->nullable();
            $table->string('site_of_admin')->nullable();
            $table->boolean('adverse_reaction')->default(false);
            $table->date('next_due_date')->nullable();
            $table->date('withdrawal_end_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_vaccine_administrations');
    }
};
