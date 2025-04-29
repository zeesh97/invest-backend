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
        Schema::create('project_mdm_software_categories', function (Blueprint $table) {
            $table->id();
            $table->string('project_name', 255)->nullable();
            $table->foreignId('mdm_category_id')->nullable()
                ->references('id')->on('mdm_categories')
                ->onUpdate('cascade');
            $table->foreignId('software_category_id')->nullable()
                ->references('id')->on('software_categories')
                ->onUpdate('cascade');
            $table->timestamps();

            $table->unique(['mdm_category_id', 'software_category_id', 'project_name'], 'unique_proj_mdm_sw_cats');
        });
    }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('project_m_d_m_s');
    // }
};
