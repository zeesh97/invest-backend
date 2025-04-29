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
                $table->unsignedBigInteger('mdm_category_id')->nullable()->before('software_category_id');
                $table->foreign('mdm_category_id')->references('id')->on('mdm_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_data_management_forms', function (Blueprint $table) {
                $table->dropForeign(['mdm_category_id']);
                $table->dropColumn('mdm_category_id');
        });
    }
};
