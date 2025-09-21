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

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // $table->unsignedBigInteger('case_reports');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->unsignedBigInteger('barangay_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('street');
            $table->string('password');
            $table->string('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending', 'rejected', 'banned'])->default('pending');
            $table->boolean('force_password_change')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('barangay_id')->references('id')->on('barangays');
            $table->foreign('role_id')->references('id')->on('roles');
        });

        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('name');
            $table->string('breed');
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->double('weight')->nullable();
            $table->double('height')->nullable();
            $table->string('color');
            $table->string('unique_spot')->nullable();
            $table->string('known_conditions')->nullable();
            $table->string('code')->unique();
            $table->string('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['name', 'type']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('animals');
        Schema::dropIfExists('users');
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('roles');
    }
};
