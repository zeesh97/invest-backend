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
        Schema::create('software_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
        Schema::create('software_subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('software_category_id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
        Schema::create('business_experts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('software_subcategory_id');
            $table->foreignId('business_expert_user_id')->nullable()
            ->references('id')->on('users')
            ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_categories');
        Schema::dropIfExists('software_subcategories');
        Schema::dropIfExists('business_experts');
    }
};
