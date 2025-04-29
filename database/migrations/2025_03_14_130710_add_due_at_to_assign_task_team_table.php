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
        Schema::table('assign_task_team', function (Blueprint $table) {
            $table->dateTime('start_at')->nullable();
            $table->dateTime('due_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::table('assign_task_team', function (Blueprint $table) {
    //         //
    //     });
    // }
};
