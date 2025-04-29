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
        Schema::table('master_data_management_forms', function (Blueprint $table) {
            $table->foreignId('mdm_project_id')
                  ->nullable()
                  ->constrained('project_mdm_software_categories')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_data_management_forms', function (Blueprint $table) {
            //
        });
    }
};
