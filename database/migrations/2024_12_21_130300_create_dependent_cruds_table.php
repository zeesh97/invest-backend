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
        Schema::create('dependent_cruds', function (Blueprint $table) {
            $table->id();
            $table->string('type', 255)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreignId('company_id')->nullable()->constrained();
            $table->json('data')->nullable();
            $table->foreign('parent_id')->references('id')->on('dependent_cruds')->onDelete('cascade');
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependent_cruds');
    }
};
