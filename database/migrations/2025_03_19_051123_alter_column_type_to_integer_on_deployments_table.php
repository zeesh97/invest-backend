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
        Schema::table('deployments', function (Blueprint $table) {
            if (Schema::hasColumn('deployments', 'reference_details')) {
                $table->unsignedBigInteger('reference_details')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            if (Schema::hasColumn('deployments', 'reference_details')) {
                $table->string('reference_details')->change();  // Reverse the change
            }
        });
    }
};
