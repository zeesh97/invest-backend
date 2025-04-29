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
        Schema::create('workflow_subscribers_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->nullable()
            ->references('id')->on('workflows')
            ->onUpdate('cascade');
            $table->foreignId('approver_id')->nullable()
            ->references('id')->on('approvers')
            ->onUpdate('cascade');
            $table->foreignId('subscriber_id')->nullable()
            ->references('id')->on('subscribers')
            ->onUpdate('cascade');
            $table->foreignId('approval_condition')->nullable()
            ->references('id')->on('conditions')
            ->onUpdate('cascade');
            $table->integer('sequence_no')->nullable();
            $table->boolean('editable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_subscribers_approvers');
    }
};
