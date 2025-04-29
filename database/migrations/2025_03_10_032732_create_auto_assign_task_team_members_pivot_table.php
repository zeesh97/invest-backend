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
        Schema::create('auto_assign_task_team_members_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auto_assign_task_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('member_id');

            $table->foreign('auto_assign_task_id')->references('id')->on('auto_assign_tasks')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('restrict');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('restrict');

            $table->unique(['auto_assign_task_id', 'team_id', 'member_id'], 'unique_task_team_member');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_assign_task_team_members_pivot');
    }
};
