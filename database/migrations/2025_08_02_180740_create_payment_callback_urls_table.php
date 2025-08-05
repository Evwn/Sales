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
        Schema::create('payment_callback_urls', function (Blueprint $table) {
            $table->id();
            
            // Foreign key relationships
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            
            // Payment type and provider information
            $table->string('payment_type'); // mpesa, card, bank_transfer, etc.
            $table->string('provider'); // safaricom, stripe, paypal, etc.
            $table->string('environment'); // sandbox, live, test
            
            // Callback URL details
            $table->string('callback_url');
            $table->string('webhook_url')->nullable(); // Alternative webhook URL
            $table->string('success_url')->nullable(); // Success redirect URL
            $table->string('failure_url')->nullable(); // Failure redirect URL
            $table->string('cancel_url')->nullable(); // Cancel redirect URL
            
            // Additional configuration
            $table->json('headers')->nullable(); // Custom headers for callback
            $table->json('metadata')->nullable(); // Additional metadata
            $table->text('description')->nullable(); // Description of the callback
            
            // Status and validation
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // Whether callback URL is verified
            $table->timestamp('last_callback_at')->nullable(); // Last time callback was received
            $table->timestamp('verified_at')->nullable(); // When callback was verified
            
            // Security and validation
            $table->string('secret_key')->nullable(); // Secret key for webhook validation
            $table->string('signature_header')->nullable(); // Header name for signature validation
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['branch_id', 'payment_type', 'environment']);
            $table->index(['business_id', 'payment_type', 'environment']);
            $table->index(['payment_type', 'provider', 'environment']);
            $table->index(['is_active', 'is_verified']);
            $table->index('callback_url');
            
            // Foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate callbacks for same branch/payment type
            $table->unique(['branch_id', 'payment_type', 'provider', 'environment'], 'unique_branch_payment_callback');
            
            // Unique constraint for business-level callbacks
            $table->unique(['business_id', 'payment_type', 'provider', 'environment'], 'unique_business_payment_callback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_callback_urls');
    }
};
