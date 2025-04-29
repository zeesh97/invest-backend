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
        Schema::create('sap_access_forms', function (Blueprint $table) {
            $table->commonColumns();
            $table->enum('type', ['New', 'Modification'])->default('New');
            $table->unsignedInteger('sap_id')->nullable();
            $table->string('roles_required')->nullable();
            $table->string('tcode_required')->nullable();
            $table->text('business_justification')->nullable();
            $table->json('data');
            $table->foreignId('company_id')->nullable()
            ->references('id')
            ->on('companies')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_access_forms');
    }
};
