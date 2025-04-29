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
        Schema::create('master_data_management_forms', function (Blueprint $table) {
            $table->commonColumns();
            $table->string('request_specs', 255)->nullable();
            $table->string('change_priority', 255)->nullable();
            $table->enum('change_significance', ['Minor', 'Major'])->default('Minor');
            $table->foreignId('software_category_id')->nullable()
            ->references('id')
            ->on('software_categories')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_management_forms');
    }
};
