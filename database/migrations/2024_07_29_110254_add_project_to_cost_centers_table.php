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
        Schema::table('cost_centers', function (Blueprint $table) {
            $table->dropForeign('cost_centers_department_id_foreign');
            $table->dropForeign('cost_centers_location_id_foreign');

            // Drop unique and regular indexes
            $table->dropUnique(['department_id', 'location_id']);
            $table->dropIndex(['department_id', 'location_id']);

            // Add the new 'project' column
            if (!Schema::hasColumn('cost_centers', 'project')) {
                $table->string('project', 255)->nullable();
            }

            // Add unique and regular indexes with 'project' column
            $table->unique(['department_id', 'location_id', 'project']);
            $table->index(['department_id', 'location_id', 'project']);

            // Recreate the foreign key constraints
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cost_centers', function (Blueprint $table) {
            // Drop the unique index and regular index with 'project' column
            $table->dropUnique(['department_id', 'location_id', 'project']);
            $table->dropIndex(['department_id', 'location_id', 'project']);

            // Drop the 'project' column
            $table->dropColumn('project');

            // Recreate the original unique index and regular index
            $table->unique(['department_id', 'location_id']);
            $table->index(['department_id', 'location_id']);

            // Recreate the foreign key constraints
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade');
        });
    }
};
