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
        Schema::create('scrf', function (Blueprint $table) {
            $table->commonColumns();
            $table->string('request_specs', 255)->nullable();
            $table->string('change_type', 255)->nullable();
            $table->string('change_priority', 255)->nullable();
            $table->double('man_hours', 8, 2)->nullable();
            $table->text('process_efficiency')->nullable();
            $table->text('controls_improved')->nullable();
            $table->text('cost_saved')->nullable();
            $table->text('legal_reasons')->nullable();
            $table->enum('change_significance', ['Minor', 'Major'])->default('Minor');
            $table->text('other_benefits')->nullable();
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
        Schema::dropIfExists('scrf');
    }
};
