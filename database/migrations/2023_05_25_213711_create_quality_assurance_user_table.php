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
        Schema::create('quality_assurance_user', function (Blueprint $table) {
            $table->unsignedBigInteger('quality_assurance_id')->nullable();
            $table->unsignedBigInteger('qa_user_id')->nullable();

            $table->string('status')->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('status_at')->nullable();

            $table->foreign('quality_assurance_id')->references('id')->on('quality_assurances')->onDelete('cascade');
            $table->foreign('qa_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_assurance_user');
    }
};
