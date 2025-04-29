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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('email_host')->nullable();
            $table->string('email_username')->nullable();
            $table->string('email_password')->nullable();
            $table->integer('email_port')->nullable();
            $table->string('email_encryption')->nullable();
            $table->string('email_mail_transport')->nullable();
            $table->string('email_mail_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['email_host', 'email_username', 'email_password', 'email_port', 'email_encryption', 'email_mail_transport',
            'email_mail_from']);
        });
    }
};
