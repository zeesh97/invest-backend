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
        Schema::create('form_permissionables', function (Blueprint $table) {
            // $table->string('name');
            $table->foreignId('form_role_id')->constrained();
            $table->foreignId('form_id')->constrained();
            $table->string('form_permissionable_type');
            $table->unsignedBigInteger('form_permissionable_id');
            $table->timestamps();
            $table->unique(
                ['form_role_id', 'form_id', 'form_permissionable_type', 'form_permissionable_id'],
                'form_permissionables_unique'
            );
            $table->index(['form_permissionable_id', 'form_permissionable_type'], 'form_permissionables_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_permissionables');
    }
};
