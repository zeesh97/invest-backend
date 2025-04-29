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
        Schema::create('mobile_requisitions', function (Blueprint $table) {
            $table->commonColumns();
            $table->foreignId('request_for_user_id')->nullable()
            ->references('id')->on('users')
            ->onUpdate('cascade');
            $table->dateTime('issue_date')->nullable();
            $table->dateTime('recieve_date')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('imei')->nullable();
            $table->string('mobile_number')->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_requisitions');
    }
};
