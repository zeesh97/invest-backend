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
        Schema::table('approval_statuses', function (Blueprint $table) {
            $table->dropForeign(['condition_id']);

            $table->foreign('condition_id')
                ->references('id')->on('conditions')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

};
