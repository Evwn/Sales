<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // For barcode/reference number
            $table->string('barcode')->unique()->nullable();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('total_quantity', 10, 2);
            $table->decimal('loyalty_points_earned', 10, 2)->default(0);
            $table->json('payment_methods'); // Store payment methods and their amounts
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'created_at']);
            $table->index(['branch_id', 'created_at']);
            $table->index('reference');
        });

        Schema::create('sales_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_receipt_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->string('product_barcode');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('barcode')->nullable();
            $table->timestamps();

            $table->index(['sales_receipt_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_receipt_items');
        Schema::dropIfExists('sales_receipts');
    }
}; 