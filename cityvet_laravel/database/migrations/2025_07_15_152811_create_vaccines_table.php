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
        Schema::create('vaccines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('brand')->nullable();
            $table->enum('category', ['vaccine', 'deworming', 'vitamin']);
            $table->integer('stock')->default(0);
            $table->string('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->string('protect_against')->nullable();
            $table->string('affected')->nullable();
            $table->date('received_date');
            $table->date('expiration_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccines');
    }
};
