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
        Schema::create('form_location_manager_team', function (Blueprint $table) {
            $table->foreignId('form_id')->nullable()
            ->references('id')->on('forms')
            ->onUpdate('cascade');
            $table->foreignId('location_id')->nullable()
            ->references('id')->on('locations')
            ->onUpdate('cascade');
            $table->foreignId('manager_id')->nullable()
            ->references('id')->on('users')
            ->onUpdate('cascade');
            $table->foreignId('team_id')->nullable()
            ->references('id')->on('teams')
            ->onUpdate('cascade');
            $table->index(['form_id', 'manager_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_location_manager_team');
    }
};
