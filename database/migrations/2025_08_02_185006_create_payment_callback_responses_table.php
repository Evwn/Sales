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
        Schema::create('payment_callback_responses', function (Blueprint $table) {
            $table->id();
            
            // Foreign key relationships
            $table->unsignedBigInteger('callback_url_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            
            // Payment identification
            $table->string('payment_type'); // mpesa, card, bank_transfer, etc.
            $table->string('provider'); // safaricom, stripe, paypal, etc.
            $table->string('environment'); // sandbox, live, test
            
            // M-PESA specific fields
            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('result_code')->nullable();
            $table->text('result_desc')->nullable();
            
            // Transaction details
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('balance')->nullable(); // Changed to text to handle complex M-PESA balance objects
            
            // Callback metadata
            $table->json('callback_data')->nullable(); // Full callback payload
            $table->json('headers')->nullable(); // Request headers
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled', 'timeout'])->default('pending');
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['callback_url_id', 'created_at'], 'callback_responses_url_created_idx');
            $table->index(['branch_id', 'payment_type', 'created_at'], 'callback_responses_branch_idx');
            $table->index(['business_id', 'payment_type', 'created_at'], 'callback_responses_business_idx');
            $table->index(['merchant_request_id', 'checkout_request_id'], 'callback_responses_request_idx');
            $table->index(['result_code', 'status'], 'callback_responses_status_idx');
            $table->index(['phone_number', 'created_at'], 'callback_responses_phone_idx');
            $table->index('status', 'callback_responses_status_only_idx');
            $table->index('created_at', 'callback_responses_created_idx');
            
            // Foreign key constraints
            $table->foreign('callback_url_id')->references('id')->on('payment_callback_urls')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_callback_responses');
    }
};
