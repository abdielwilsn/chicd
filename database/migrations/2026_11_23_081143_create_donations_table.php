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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->string('fund');
            $table->string('fund_label');
            $table->string('frequency');
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3);
            $table->string('donor_name');
            $table->string('donor_email');
            $table->string('donor_phone')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('paystack_transaction_id')->nullable();
            $table->string('paystack_customer_code')->nullable();
            $table->string('paystack_plan_code')->nullable();
            $table->string('paystack_subscription_code')->nullable();
            $table->string('channel')->nullable();
            $table->unsignedBigInteger('fees')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'paid_at']);
            $table->index(['status', 'created_at']);
            $table->index(['paystack_customer_code', 'paystack_plan_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
