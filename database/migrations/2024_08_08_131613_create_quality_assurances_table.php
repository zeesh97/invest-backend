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
        Schema::create('quality_assurances', function (Blueprint $table) {
            $table->commonColumns();
            $table->unsignedBigInteger('qa_assignment_id')->nullable();

            $table->foreign('qa_assignment_id')->references('id')->on('qa_assignments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_assurances');
    }
};
