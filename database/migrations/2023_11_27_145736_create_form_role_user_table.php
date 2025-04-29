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
        Schema::create('form_role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_role_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unique(['form_role_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_role_user');
    }
};
