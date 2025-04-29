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
        Schema::table('crfs', function (Blueprint $table) {
            $table->string('for_employee', 150)->nullable();
            $table->unsignedBigInteger('for_department')->nullable();

            $table->foreign('for_department')
                ->references('id')
                ->on('departments')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crfs', function (Blueprint $table) {
            $table->dropColumn('for_employee');

            $table->dropForeign(['for_department']);
            $table->dropColumn('for_department');
        });
    }
};
