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
        Schema::create('document_details_deployment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deployment_id')->nullable()
            ->references('id')->on('deployments')
            ->onUpdate('cascade');
            $table->string('document_no')->nullable();
            $table->text('detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_details_deployment');
    }
};
