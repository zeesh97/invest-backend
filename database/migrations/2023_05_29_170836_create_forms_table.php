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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('identity')->unique();
            $table->string('initiator_field_one_id', 255)->nullable();
            $table->string('initiator_field_two_id', 255)->nullable();
            $table->string('initiator_field_three_id', 255)->nullable();
            $table->string('initiator_field_four_id', 255)->nullable();
            $table->string('initiator_field_five_id', 255)->nullable();
            $table->string('callback', 255)->nullable();

            // $table->string('condition_string_one', 255)->nullable();
            // $table->string('condition_string_two', 255)->nullable();
            // $table->string('condition_string_three', 255)->nullable();
            // $table->string('condition_number_one', 255)->nullable();
            // $table->string('condition_number_two', 255)->nullable();
            // $table->string('condition_number_three', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
