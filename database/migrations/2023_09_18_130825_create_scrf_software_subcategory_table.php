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
        Schema::create('scrf_software_subcategory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrf_id')->nullable()
            ->references('id')->on('scrf')
            ->onUpdate('cascade');
            $table->foreignId('software_subcategory_id')->nullable()
            ->references('id')->on('software_subcategories')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrf_software_subcategory');
    }
};
