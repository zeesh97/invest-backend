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
        Schema::table('support_desk_forms', function (Blueprint $table) {
            $table->dropColumn('service_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_desk_forms', function (Blueprint $table) {
            $table->string('service_required')->nullable();

            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }
};
