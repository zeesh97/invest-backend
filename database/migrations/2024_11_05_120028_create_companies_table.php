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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('logo', 2048)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('long_name', 500)->nullable();
            $table->string('ntn_no', 50)->nullable();
            $table->string('sales_tax_no', 50)->nullable();
            $table->string('postal_code', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('phone', 50)->nullable();
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
