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
        Schema::dropIfExists('quality_assurance_user');
        Schema::dropIfExists('quality_assurances');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
