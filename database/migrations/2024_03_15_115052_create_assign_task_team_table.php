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
        Schema::create('assign_task_team', function (Blueprint $table) {
            $table->unsignedBigInteger('assign_task_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('member_id');

            $table->foreign('assign_task_id')->references('id')->on('assign_tasks')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_task_team_user_pivot');
    }
};
