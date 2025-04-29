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
        Schema::table('equipment_requests', function (Blueprint $table) {
            $table->char('currency_default', 3)->nullable()->default('PKR')->after('currency');
        });
        Schema::table('software_requests', function (Blueprint $table) {
            $table->char('currency_default', 3)->nullable()->default('PKR')->after('currency');
        });
        Schema::table('service_requests', function (Blueprint $table) {
            $table->char('currency_default', 3)->nullable()->default('PKR')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alter_crf');
    }
};
