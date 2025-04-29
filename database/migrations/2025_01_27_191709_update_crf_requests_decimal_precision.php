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
        Schema::table('equipment_requests', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
            $table->decimal('rate', 20, 2)->change();
            $table->decimal('total', 20, 2)->change();
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
            $table->decimal('rate', 20, 2)->change();
            $table->decimal('total', 20, 2)->change();
        });

        Schema::table('software_requests', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
            $table->decimal('rate', 20, 2)->change();
            $table->decimal('total', 20, 2)->change();
        });
    }

};
