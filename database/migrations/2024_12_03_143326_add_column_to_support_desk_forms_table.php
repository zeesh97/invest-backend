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

            $table->foreignId('service_id')
                ->nullable()
                ->after('department_id')
                ->constrained('services')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_desk_forms', function (Blueprint $table) {
            $table->dropColumn('data');
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }
};
