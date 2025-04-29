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
            $table->text('request_specs')->change();
            $table->text('change_priority')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_data_management_forms', function (Blueprint $table) {
            $table->string('request_specs', 255)->change();
            $table->string('change_priority', 255)->change();
        });
    }
};
