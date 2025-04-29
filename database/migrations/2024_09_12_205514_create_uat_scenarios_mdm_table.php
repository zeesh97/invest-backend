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
        Schema::create('uat_scenarios_mdm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_data_management_form_id')->nullable()
            ->references('id')->on('master_data_management_forms')
            ->onUpdate('cascade');
            $table->text('detail');
            $table->enum('status', ['Pass', 'Fail'])->default('Pass');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uat_scenarios_mdm');
    }
};
