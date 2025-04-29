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
        Schema::table('request_support_forms', function (Blueprint $table) {
            Schema::rename('support_desk_forms', 'request_support_forms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_support_forms', function (Blueprint $table) {
            Schema::rename('support_desk_forms', 'request_support_forms');
        });
    }
};
