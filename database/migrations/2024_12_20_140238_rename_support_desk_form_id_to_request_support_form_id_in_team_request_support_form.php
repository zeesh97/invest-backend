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
        Schema::table('team_request_support_form', function (Blueprint $table) {
            $table->renameColumn('support_desk_form_id', 'request_support_form_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_request_support_form', function (Blueprint $table) {
            $table->renameColumn('request_support_form_id', 'support_desk_form_id');
        });
    }
};
