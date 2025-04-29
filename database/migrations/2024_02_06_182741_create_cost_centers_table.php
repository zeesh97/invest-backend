<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('cost_center', 255)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()
                ->references('id')->on('departments')
                ->onUpdate('cascade');
            $table->foreignId('location_id')->nullable()
                ->references('id')->on('locations')
                ->onUpdate('cascade');
            $table->timestamps();
            $table->unique(['department_id', 'location_id']);
            $table->index(['department_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
