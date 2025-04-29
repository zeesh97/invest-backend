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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->foreignId('department_id')->nullable()
            ->references('id')->on('departments')
            ->onUpdate('cascade');
            $table->foreignId('designation_id')->nullable()
            ->references('id')->on('designations')
            ->onUpdate('cascade');
            $table->foreignId('location_id')->nullable()
            ->references('id')->on('locations')
            ->onUpdate('cascade');
            $table->foreignId('section_id')->nullable()
            ->references('id')->on('sections')
            ->onUpdate('cascade');
            $table->string('employee_no', 50)->default('emp-161610');
            $table->string('employee_type', 50)->default('permanent');
            $table->string('phone_number')->nullable();
            $table->string('extension')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
