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
        Schema::create('workflow_initiator_fields', function (Blueprint $table) {
            $table->foreignId('workflow_id')->nullable()
                ->references('id')->on('workflows')
                ->onUpdate('cascade');
            $table->foreignId('form_id')->nullable()
                ->references('id')->on('forms')
                ->onUpdate('cascade');
            $table->foreignId('initiator_id')->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade');
            $table->unsignedInteger('key_one')->nullable();
            $table->unsignedInteger('key_two')->nullable();
            $table->unsignedInteger('key_three')->nullable();
            $table->unsignedInteger('key_four')->nullable();
            $table->unsignedInteger('key_five')->nullable();

            // Adding a unique constraint for the specified columns
            $table->unique(['form_id', 'initiator_id', 'key_one', 'key_two', 'key_three', 'key_four', 'key_five'], 'unique_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_initiator_fields');
    }
};

