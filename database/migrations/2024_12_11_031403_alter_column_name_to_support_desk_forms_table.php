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
            $table->dropColumn('sap_id');
            $table->string('relevant_id')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_desk_forms', function (Blueprint $table) {
            $table->unsignedInteger('sap_id')->nullable();
            $table->dropColumn('relevant_id');
        });
    }
};
