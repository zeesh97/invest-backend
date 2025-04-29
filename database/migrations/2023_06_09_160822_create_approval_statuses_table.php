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
        Schema::create('approval_statuses', function (Blueprint $table) {
            $table->foreignId('workflow_id')->nullable()
            ->references('id')->on('workflows')
            ->onUpdate('cascade');
            $table->foreignId('approver_id')->nullable()
            ->references('id')->on('approvers')
            ->onUpdate('cascade');
            $table->foreignId('user_id')->nullable()
            ->references('id')->on('users')
            ->onUpdate('cascade');
            $table->boolean('approval_required')->default(0);
            $table->unsignedInteger('sequence_no')->default(null)->nullable();
            $table->foreignId('condition_id')->nullable()
            ->references('id')->on('conditions')
            ->onUpdate('cascade');
            $table->foreignId('form_id')->nullable()
            ->references('id')->on('forms')
            ->onUpdate('cascade');
            $table->unsignedBigInteger('key')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('status_at')->nullable();
            $table->foreignId('responded_by')->nullable()
            ->references('id')->on('users')
            ->onUpdate('cascade');
            $table->boolean('editable')->default(false);

            $table->index(['form_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approver_statuses');
    }
};
