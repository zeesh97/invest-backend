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
        Schema::table('workflow_initiator_fields', function (Blueprint $table) {

            $table->string('initiator_field_one_id', 255)->nullable();
            $table->string('initiator_field_two_id', 255)->nullable();
            $table->string('initiator_field_three_id', 255)->nullable();
            $table->string('initiator_field_four_id', 255)->nullable();
            $table->string('initiator_field_five_id', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_initiator_fields', function (Blueprint $table) {
            //
        });
    }
};
