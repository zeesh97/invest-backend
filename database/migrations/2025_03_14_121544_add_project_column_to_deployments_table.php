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
                if (Schema::hasColumn('deployments', 'form_id')) {
                    // Drop foreign key if it exists
                    // $table->dropForeign(['form_id']);
                    $table->dropColumn('form_id');
                }

                if (Schema::hasColumn('deployments', 'project_id') && !Schema::hasColumn('deployments', 'reference_form_id')) {
                    // Drop foreign key first, then the column
                    $table->dropForeign(['project_id']);
                    $table->dropColumn('project_id');
                }

                if (!Schema::hasColumn('deployments', 'reference_form_id')) {
                    $table->foreignId('reference_form_id')
                        ->nullable()
                        ->constrained('forms')
                        ->onDelete('cascade')
                        ->after('workflow_id');
                }

                if (!Schema::hasColumn('deployments', 'project_id')) {
                    $table->foreignId('project_id')
                        ->nullable()
                        ->constrained('projects')
                        ->onDelete('cascade')
                        ->after('reference_form_id');
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            //
        });
    }
};
