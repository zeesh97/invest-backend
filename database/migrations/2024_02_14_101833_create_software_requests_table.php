<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('software_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crf_id')
                ->references('id')->on('crfs')
                ->onUpdate('cascade');
            $table->string('name', 250)->nullable();
            $table->string('version', 250)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedTinyInteger('expense_type')->nullable();
            $table->unsignedTinyInteger('expense_nature')->nullable();
            $table->text('business_justification')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency', 250)->nullable();
            $table->decimal('rate', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->json('asset_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_requests');
    }
};
