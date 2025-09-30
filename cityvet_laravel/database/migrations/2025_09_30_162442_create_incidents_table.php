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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('victim_name');
            $table->integer('age');
            $table->string('species');
            $table->string('bite_provocation');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('location_address');
            $table->timestamp('incident_time');
            $table->text('remarks')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('reported_at')->useCurrent();
            $table->string('reported_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
