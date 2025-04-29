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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('expired_at')->nullable();
            $table->decimal('price', 8, 2);
            $table->integer('number_of_transactions')->default(0);
            $table->integer('data_mb')->default(0);
            $table->integer('total_users')->default(0);
            $table->integer('login_users')->default(0);
            $table->integer('usage_number_of_transactions')->default(0);
            $table->integer('usage_data_mb')->default(0);
            $table->integer('usage_total_users')->default(0);
            $table->integer('usage_login_users')->default(0);
            $table->foreignId('transaction_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
