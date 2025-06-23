<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Financial Management
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['bank', 'cash', 'mpesa']);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->constrained('accounts');
            $table->foreignId('to_account_id')->constrained('accounts');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // MPESA Integration
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained();
            $table->string('mpesa_reference')->unique();
            $table->string('phone_number');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // Manufacturing
        Schema::create('manufacturing_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('manufacturing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });

        // Enhanced Sales & Purchases
        Schema::create('sales_commission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users');
            $table->unsignedBigInteger('sale_id');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // Documents
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->dateTime('valid_until');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('sale_id');
            $table->enum('status', ['draft', 'sent', 'delivered'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // Communications
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('recipient_type', ['customer', 'supplier']);
            $table->unsignedBigInteger('recipient_id');
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // TIMS Integration
        Schema::create('tims_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('type', ['sale', 'purchase']);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vat_returns', function (Blueprint $table) {
            $table->id();
            $table->string('period');
            $table->decimal('total_sales', 15, 2);
            $table->decimal('total_purchases', 15, 2);
            $table->decimal('vat_amount', 15, 2);
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // Analytics
        Schema::create('product_movement_analysis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('period');
            $table->decimal('quantity_sold', 15, 2)->default(0);
            $table->decimal('quantity_purchased', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_valuation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 15, 2);
            $table->decimal('value', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_valuation');
        Schema::dropIfExists('product_movement_analysis');
        Schema::dropIfExists('vat_returns');
        Schema::dropIfExists('tims_transactions');
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('sales_commission');
        Schema::dropIfExists('manufacturing_items');
        Schema::dropIfExists('manufacturing_orders');
        Schema::dropIfExists('mpesa_transactions');
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('accounts');
    }
}; 