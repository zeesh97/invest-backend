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
        Schema::create('approver_users', function (Blueprint $table) {

            $table->foreignId('approver_id')->nullable()
            ->references('id')->on('approvers')
            ->onUpdate('cascade');
            $table->foreignId('user_id')->nullable()
            ->references('id')->on('users')
            ->onUpdate('cascade');
            $table->boolean('approval_required')->default(0);
            $table->unsignedInteger('sequence_no')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approver_users');
    }
};
