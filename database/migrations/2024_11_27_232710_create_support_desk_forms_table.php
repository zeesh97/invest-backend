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
        Schema::create('support_desk_forms', function (Blueprint $table) {
            $table->id();

            $table->string('sequence_no', 255)->nullable()->unique();
            $table->string('request_title', 255)->nullable();
            $table->unsignedInteger('sap_id')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Low');
            $table->string('phone', 255)->nullable();
            $table->string('service_required')->nullable();
            $table->foreignId('department_id')->nullable()
                ->references('id')->on('departments')
                ->onUpdate('cascade');
                $table->foreignId('location_id')->nullable()
                ->references('id')->on('locations')
                ->onUpdate('cascade');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade');
            $table->foreignId('updated_by')->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade');
            $table->timestamp('draft_at')->nullable();
            $table->timestamp('task_status_at')->nullable();
            $table->foreignId('task_status')->nullable()
                ->references('id')->on('task_status_names')
                ->onUpdate('cascade');
            $table->string('status')->nullable();
            $table->boolean('comment_status')->nullable()->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_desk_forms');
    }
};
