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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('reason');
            $table->text('details');
            $table->unsignedBigInteger('barangay_id');
            $table->time('time');
            $table->date('date');
            $table->enum('status', ['up_coming', 'on_going', 'completed', 'failed'])->default('up_coming');
            $table->timestamps();

            $table->foreign('barangay_id')->references('id')->on('barangays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
